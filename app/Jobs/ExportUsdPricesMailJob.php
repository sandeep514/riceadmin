<?php

namespace App\Jobs;

use App\Export\CalculatorExport;
use App\Support\QueuedMail;
use App\Support\UsdPricePackingFilter;
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
            true,
            true
        );

        $rowCount = (clone $export->query())->count();
        if ($rowCount === 0) {
            Log::info('ExportUsdPricesMailJob skipped: no matching records', [
                'email' => $this->email,
                'from' => $this->fromDate,
                'to' => $this->toDate,
                'packingIds' => $this->packingIds,
                'packingKey' => $this->packingKey,
                'type' => $this->type,
            ]);

            return;
        }

        $filters = $this->filterSummary();

        try {
            @ini_set('memory_limit', '512M');

            $packingSlug = $this->packingKey
                ? '_'.Str::slug($this->packingKey)
                : ($this->packingIds ? '_packing-'.implode('-', $this->packingIds) : '');

            $filename = 'SNTC_usd_prices_'.Str::slug(($this->fromDate ?? 'start').'_'.($this->toDate ?? 'end'))
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
                    ->from('info@sntcgroup.com', QueuedMail::normalizeSntcFromName('SNTC Team - India'))
                    ->subject(QueuedMail::normalizeSntcSubject('USD Prices Export'))
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
        if ($this->packingKey && ! UsdPricePackingFilter::isAllKey($this->packingKey)) {
            $packingLabel = UsdPricePackingFilter::labelForKey($this->packingKey);
        } else {
            $packingLabel = UsdPricePackingFilter::labelsForIds($this->packingIds);
        }

        return [
            'From' => $this->fromDate ?: 'All',
            'To' => $this->toDate ?: 'All',
            'Type' => $this->type ?: 'All',
            'Packing' => $packingLabel,
        ];
    }
}
