<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebUserSubscriptionModel extends Model
{
    protected $table = 'web_user_subscription';
    protected $fillable = [
        'user_id',
        'plan_id',
        'period_start',
        'period_end',
        'payment_id',
        'order_id',
        'subscription_type',
        'amount',
        'currency',
        'invoice_path',
        'status',
    ];

    public function planRel()
    {
        return $this->belongsTo(WebPlanModel::class, 'plan_id', 'id');
    }

    public function userRel()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function invoiceAbsolutePath(): ?string
    {
        if (! empty($this->invoice_path)) {
            $path = storage_path('app/'.$this->invoice_path);
            if (is_file($path)) {
                return $path;
            }
        }

        $matches = glob(storage_path('app/invoices/SNTC-Invoice-'.$this->id.'-*.pdf')) ?: [];
        if ($matches) {
            rsort($matches);

            return $matches[0];
        }

        return null;
    }

    public function hasDownloadableInvoice(): bool
    {
        return strtolower((string) $this->subscription_type) !== 'trial';
    }

    public function invoiceNumber(): ?string
    {
        if (! $this->hasDownloadableInvoice()) {
            return null;
        }

        $year = $this->created_at
            ? \Carbon\Carbon::parse($this->created_at)->format('Y')
            : date('Y');

        return 'SNTC/INV/'.$year.'/'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function displayAmount(): ?float
    {
        if ($this->amount !== null && $this->amount !== '') {
            return (float) $this->amount;
        }

        $plan = $this->planRel;
        if (! $plan) {
            return null;
        }

        $type = (string) $this->subscription_type;
        if ($type === 'yearly') {
            return (float) ($plan->yearly_final_amount ?? $plan->yearly_price ?? 0);
        }
        if (in_array($type, ['quarterly', 'half_yearly'], true)) {
            return (float) ($plan->quarterly_final_amount ?? $plan->quarterly_price ?? 0);
        }
        if ($type === 'monthly') {
            return (float) ($plan->monthly_final_amount ?? $plan->monthly_price ?? 0);
        }

        return null;
    }
}