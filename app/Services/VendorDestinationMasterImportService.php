<?php

namespace App\Services;

use App\VendorDestinationCountry;
use App\VendorDestinationPort;
use App\VendorDestinationRegion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class VendorDestinationMasterImportService
{
    /**
     * @return array{
     *     rows_read:int,
     *     regions_created:int,
     *     countries_created:int,
     *     ports_created:int,
     *     regions_skipped:int,
     *     countries_skipped:int,
     *     ports_skipped:int,
     *     rows_skipped:int
     * }
     */
    public function import(UploadedFile $file): array
    {
        $sheets = Excel::toArray([], $file);
        $rows = $sheets[0] ?? [];

        if (count($rows) === 0) {
            throw new \RuntimeException('The Excel file is empty.');
        }

        return DB::transaction(fn () => $this->importRows($rows));
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @return array{
     *     rows_read:int,
     *     regions_created:int,
     *     countries_created:int,
     *     ports_created:int,
     *     regions_skipped:int,
     *     countries_skipped:int,
     *     ports_skipped:int,
     *     rows_skipped:int
     * }
     */
    public function importRows(array $rows): array
    {
        [$columnMap, $startIndex] = $this->resolveColumns($rows);

        $regionCache = [];
        $countryCache = [];
        $portCache = [];

        foreach (VendorDestinationRegion::query()->get(['id', 'name']) as $region) {
            $regionCache[$this->cacheKey($region->name)] = (int) $region->id;
        }

        foreach (VendorDestinationCountry::query()->get(['id', 'region_id', 'name']) as $country) {
            $countryCache[$this->countryCacheKey((int) $country->region_id, $country->name)] = (int) $country->id;
        }

        foreach (VendorDestinationPort::query()->get(['id', 'country_id', 'name']) as $port) {
            $portCache[$this->portCacheKey((int) $port->country_id, $port->name)] = (int) $port->id;
        }

        $stats = [
            'rows_read' => 0,
            'regions_created' => 0,
            'countries_created' => 0,
            'ports_created' => 0,
            'regions_skipped' => 0,
            'countries_skipped' => 0,
            'ports_skipped' => 0,
            'rows_skipped' => 0,
        ];

        $lastRegion = '';
        $lastCountry = '';

        for ($i = $startIndex, $len = count($rows); $i < $len; $i++) {
            $row = $rows[$i] ?? [];
            if (! is_array($row) || count(array_filter($row, fn ($value) => $this->cell($value) !== '')) === 0) {
                continue;
            }

            $regionName = $this->cell($row[$columnMap['region']] ?? null);
            $countryName = $this->cell($row[$columnMap['country']] ?? null);
            $portName = $this->cell($row[$columnMap['port']] ?? null);

            if ($regionName === '' && $lastRegion !== '') {
                $regionName = $lastRegion;
            }
            if ($countryName === '' && $lastCountry !== '') {
                $countryName = $lastCountry;
            }

            if ($this->isHeaderValue($regionName) || $this->isHeaderValue($countryName) || $this->isHeaderValue($portName)) {
                $stats['rows_skipped']++;
                continue;
            }

            if ($regionName === '' || $countryName === '' || $portName === '') {
                $stats['rows_skipped']++;
                continue;
            }

            $lastRegion = $regionName;
            $lastCountry = $countryName;
            $stats['rows_read']++;

            $regionKey = $this->cacheKey($regionName);
            if (! isset($regionCache[$regionKey])) {
                $region = VendorDestinationRegion::create([
                    'name' => $regionName,
                    'status' => VendorDestinationRegion::STATUS_ACTIVE,
                ]);
                $regionCache[$regionKey] = (int) $region->id;
                $stats['regions_created']++;
            } else {
                $stats['regions_skipped']++;
            }
            $regionId = $regionCache[$regionKey];

            $countryKey = $this->countryCacheKey($regionId, $countryName);
            if (! isset($countryCache[$countryKey])) {
                $country = VendorDestinationCountry::create([
                    'region_id' => $regionId,
                    'name' => $countryName,
                    'status' => VendorDestinationCountry::STATUS_ACTIVE,
                ]);
                $countryCache[$countryKey] = (int) $country->id;
                $stats['countries_created']++;
            } else {
                $stats['countries_skipped']++;
            }
            $countryId = $countryCache[$countryKey];

            $portKey = $this->portCacheKey($countryId, $portName);
            if (! isset($portCache[$portKey])) {
                VendorDestinationPort::create([
                    'region_id' => $regionId,
                    'country_id' => $countryId,
                    'name' => $portName,
                    'status' => VendorDestinationPort::STATUS_ACTIVE,
                ]);
                $portCache[$portKey] = 1;
                $stats['ports_created']++;
            } else {
                $stats['ports_skipped']++;
            }
        }

        if ($stats['rows_read'] === 0) {
            throw new \RuntimeException('No valid rows found. Expected columns: Regions, Country, Sea Port.');
        }

        return $stats;
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @return array{0: array{region:int, country:int, port:int}, 1: int}
     */
    private function resolveColumns(array $rows): array
    {
        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $map = $this->mapHeaderRow($row);
            if ($map !== null) {
                return [$map, $index + 1];
            }
        }

        return [
            ['region' => 1, 'country' => 2, 'port' => 3],
            0,
        ];
    }

    /**
     * @param  array<int, mixed>  $row
     * @return array{region:int, country:int, port:int}|null
     */
    private function mapHeaderRow(array $row): ?array
    {
        $map = [];

        foreach ($row as $index => $value) {
            $header = $this->normalizeHeader($value);
            if (in_array($header, ['region', 'regions'], true)) {
                $map['region'] = (int) $index;
            }
            if (in_array($header, ['country', 'countries'], true)) {
                $map['country'] = (int) $index;
            }
            if (in_array($header, ['sea port', 'seaport', 'port', 'destination port', 'sea ports', 'ports'], true)) {
                $map['port'] = (int) $index;
            }
        }

        if (isset($map['region'], $map['country'], $map['port'])) {
            return $map;
        }

        return null;
    }

    private function cell(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    private function normalizeHeader(mixed $value): string
    {
        $header = strtolower($this->cell($value));
        $header = preg_replace('/\s+/', ' ', $header) ?? $header;

        return rtrim($header, '.:');
    }

    private function isHeaderValue(string $value): bool
    {
        $normalized = $this->normalizeHeader($value);

        return in_array($normalized, [
            'sr no',
            'sr. no',
            's no',
            'sno',
            'region',
            'regions',
            'country',
            'countries',
            'sea port',
            'seaport',
            'port',
            'destination port',
            'sea ports',
            'ports',
        ], true);
    }

    private function cacheKey(string $name): string
    {
        return mb_strtolower(trim($name));
    }

    private function countryCacheKey(int $regionId, string $name): string
    {
        return $regionId.'|'.$this->cacheKey($name);
    }

    private function portCacheKey(int $countryId, string $name): string
    {
        return $countryId.'|'.$this->cacheKey($name);
    }
}
