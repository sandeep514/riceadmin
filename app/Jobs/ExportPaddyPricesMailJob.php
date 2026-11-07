<?php

namespace App\Jobs;

use App\Export\PaddyPricesApiExport;
use App\PaddyQuality;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Support\QueuedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;

class ExportPaddyPricesMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    /**
     * @param  list<int>|null  $qualityIds
     */
    public function __construct(
        public string $email,
        public ?string $fromDate = null,
        public ?string $toDate = null,
        public ?array $qualityIds = null,
        public ?string $type = null,
        public ?string $seedKey = null,
        public ?string $range = null
    ) {
    }

    public function handle(): void
    {
        $export = new PaddyPricesApiExport(
            $this->fromDate,
            $this->toDate,
            $this->qualityIds
        );

        $rowCount = (clone $export->query())->count();
        if ($rowCount === 0) {
            Log::info('ExportPaddyPricesMailJob skipped: no matching records', [
                'email' => $this->email,
                'from' => $this->fromDate,
                'to' => $this->toDate,
                'qualityIds' => $this->qualityIds,
                'seedKey' => $this->seedKey,
                'type' => $this->type,
                'range' => $this->range,
            ]);

            return;
        }

        $filters = $this->filterSummary();

        try {
            @ini_set('memory_limit', '512M');

            $seedSlug = $this->seedKey
                ? '_'.Str::slug($this->seedKey)
                : ($this->qualityIds ? '_seed-'.implode('-', $this->qualityIds) : '');

            $filename = 'SNTC_paddy_prices_'.Str::slug(($this->fromDate ?? 'start').'_'.($this->toDate ?? 'end'))
                .$seedSlug
                .($this->type ? '_'.Str::slug($this->type) : '')
                .'_'.now()->format('YmdHis').'.xlsx';

            $xlsxBinary = Excel::raw($export, ExcelWriter::XLSX);

            Mail::send('mail.paddyPricesExport', [
                'hasAttachment' => true,
                'rowCount' => $rowCount,
                'filters' => $filters,
            ], function ($message) use ($xlsxBinary, $filename) {
                $message->to($this->email)
                    ->from('info@sntcgroup.com', QueuedMail::normalizeSntcFromName('SNTC Team - India'))
                    ->subject(QueuedMail::normalizeSntcSubject('Paddy Prices Export'))
                    ->attachData($xlsxBinary, $filename, [
                        'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
            });
        } catch (\Throwable $e) {
            Log::error('ExportPaddyPricesMailJob failed: '.$e->getMessage(), [
                'email' => $this->email,
                'from' => $this->fromDate,
                'to' => $this->toDate,
                'qualityIds' => $this->qualityIds,
                'seedKey' => $this->seedKey,
                'type' => $this->type,
                'range' => $this->range,
            ]);
            throw $e;
        }
    }

    /**
     * @return array<string, string>
     */
    private function filterSummary(): array
    {
        if ($this->seedKey) {
            $seedLabel = $this->seedKey;
        } elseif (is_array($this->qualityIds) && count($this->qualityIds) === 1) {
            $quality = PaddyQuality::query()->find($this->qualityIds[0]);
            $seedLabel = $quality ? (string) $quality->quality : (string) $this->qualityIds[0];
        } elseif (is_array($this->qualityIds) && count($this->qualityIds) > 1) {
            $seedLabel = 'Selected seeds ('.count($this->qualityIds).')';
        } else {
            $seedLabel = 'All';
        }

        if ($this->range === 'today') {
            $dateRange = 'Today';
        } elseif ($this->fromDate || $this->toDate) {
            $dateRange = trim(($this->fromDate ?: 'start').' to '.($this->toDate ?: 'end'));
        } elseif ($this->range === 'historical') {
            $dateRange = 'Historical';
        } else {
            $dateRange = 'All';
        }

        $typeLabel = match ($this->type) {
            'basmati' => 'Basmati',
            'non-basmati' => 'Non-Basmati',
            default => 'All',
        };

        return [
            'Date Range' => $dateRange,
            'Type' => $typeLabel,
            'Seed' => $seedLabel,
        ];
    }
}
