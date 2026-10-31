<?php

namespace App\Jobs;

use App\Export\CalculatorExport;
use App\Support\UsdPricePackingFilter;
use App\USD_defaultmaster;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;

class ExportUsdPricesMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    /**
     * @param  list<int>|null  $packingIds
     */
    public function __construct(
        public string $email,
        public ?string $fromDate = null,
        public ?string $toDate = null,
        public ?array $packingIds = null,
        public ?string $type = null,
        public ?string $packingKey = null
    ) {
    }

    public function handle(): void
    {
        $export = new CalculatorExport(
            $this->fromDate,
            $this->toDate,
            $this->packingIds,
            $this->type,
            true
        );

        $rowCount = (clone $export->query())->count();
        $filters = $this->filterSummary();

        try {
            if ($rowCount === 0) {
                Mail::send('mail.usdPricesExport', [
                    'hasAttachment' => false,
                    'rowCount' => 0,
                    'filters' => $filters,
                ], function ($message) {
                    $message->to($this->email)
                        ->subject('USD Prices Export — no rows for selected filters');
                });

                return;
            }

            @ini_set('memory_limit', '512M');

            $packingSlug = $this->packingKey
                ? '_'.Str::slug($this->packingKey)
                : ($this->packingIds ? '_packing-'.implode('-', $this->packingIds) : '');

            $filename = 'usd_prices_'.Str::slug(($this->fromDate ?? 'start').'_'.($this->toDate ?? 'end'))
                .$packingSlug
                .($this->type ? '_'.Str::slug($this->type) : '')
                .'_'.now()->format('YmdHis').'.xlsx';

            // Build XLSX in memory and attach directly — no local storage write.
            $xlsxBinary = Excel::raw($export, ExcelWriter::XLSX);

            Mail::send('mail.usdPricesExport', [
                'hasAttachment' => true,
                'rowCount' => $rowCount,
                'filters' => $filters,
            ], function ($message) use ($xlsxBinary, $filename) {
                $message->to($this->email)
                    ->subject('USD Prices Export')
                    ->attachData($xlsxBinary, $filename, [
                        'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
            });
        } catch (\Throwable $e) {
            Log::error('ExportUsdPricesMailJob failed: '.$e->getMessage(), [
                'email' => $this->email,
                'from' => $this->fromDate,
                'to' => $this->toDate,
                'packingIds' => $this->packingIds,
                'packingKey' => $this->packingKey,
                'type' => $this->type,
            ]);
            throw $e;
        }
    }

    /**
     * @return array<string, string>
     */
    private function filterSummary(): array
    {
        if ($this->packingKey) {
            $packingLabel = UsdPricePackingFilter::labelForKey($this->packingKey);
        } elseif (is_array($this->packingIds) && count($this->packingIds) === 1) {
            $pack = USD_defaultmaster::query()->find($this->packingIds[0]);
            $packingLabel = $pack
                ? trim(($pack->bag_size ?? '').' - '.($pack->bag_type ?? ''))
                : (string) $this->packingIds[0];
        } elseif (is_array($this->packingIds) && count($this->packingIds) > 1) {
            $packingLabel = 'Selected packings ('.count($this->packingIds).')';
        } else {
            $packingLabel = 'All';
        }

        return [
            'From' => $this->fromDate ?: 'All',
            'To' => $this->toDate ?: 'All',
            'Type' => $this->type ?: 'All',
            'Packing' => $packingLabel,
        ];
    }
}
