<?php

namespace App\Jobs;

use App\Export\CalculatorExport;
use App\USD_defaultmaster;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ExportUsdPricesMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public string $email,
        public ?string $fromDate = null,
        public ?string $toDate = null,
        public ?int $packingId = null,
        public ?string $type = null
    ) {
    }

    public function handle(): void
    {
        $export = new CalculatorExport(
            $this->fromDate,
            $this->toDate,
            $this->packingId,
            $this->type,
            true
        );

        $rowCount = (clone $export->query())->count();
        $filters = $this->filterSummary();
        $relativePath = null;

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

            $filename = 'usd_prices_'.Str::slug(($this->fromDate ?? 'start').'_'.($this->toDate ?? 'end'))
                .($this->packingId ? '_packing-'.$this->packingId : '')
                .($this->type ? '_'.Str::slug($this->type) : '')
                .'_'.now()->format('YmdHis').'.xlsx';

            $relativePath = 'exports/'.$filename;
            Excel::store($export, $relativePath, 'local');

            $absolutePath = Storage::disk('local')->path($relativePath);

            Mail::send('mail.usdPricesExport', [
                'hasAttachment' => true,
                'rowCount' => $rowCount,
                'filters' => $filters,
            ], function ($message) use ($absolutePath, $filename) {
                $message->to($this->email)
                    ->subject('USD Prices Export')
                    ->attach($absolutePath, [
                        'as' => $filename,
                        'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
            });
        } catch (\Throwable $e) {
            Log::error('ExportUsdPricesMailJob failed: '.$e->getMessage(), [
                'email' => $this->email,
                'from' => $this->fromDate,
                'to' => $this->toDate,
                'packing' => $this->packingId,
                'type' => $this->type,
            ]);
            throw $e;
        } finally {
            if ($relativePath && Storage::disk('local')->exists($relativePath)) {
                Storage::disk('local')->delete($relativePath);
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private function filterSummary(): array
    {
        $packingLabel = 'All';
        if ($this->packingId) {
            $pack = USD_defaultmaster::query()->find($this->packingId);
            $packingLabel = $pack
                ? trim(($pack->bag_size ?? '').' - '.($pack->bag_type ?? ''))
                : (string) $this->packingId;
        }

        return [
            'From' => $this->fromDate ?: 'All',
            'To' => $this->toDate ?: 'All',
            'Type' => $this->type ?: 'All',
            'Packing' => $packingLabel,
        ];
    }
}
