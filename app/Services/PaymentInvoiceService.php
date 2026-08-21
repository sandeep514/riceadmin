<?php

namespace App\Services;

use App\Support\SimplePdf;
use App\User;
use App\WebBusinessDetails;
use App\WebPersonalDetails;
use App\WebPlanModel;
use App\WebUserSubscriptionModel;
use Carbon\Carbon;

class PaymentInvoiceService
{
    /**
     * Build invoice data, write a PDF, and return payload + path.
     *
     * @return array{path:string,filename:string,data:array}|null
     */
    public function makePdf(
        User $user,
        WebUserSubscriptionModel $subscription,
        ?WebPlanModel $plan,
        float $paidTotal,
        string $currency = 'INR'
    ): ?array {
        $data = $this->buildData($user, $subscription, $plan, $paidTotal, $currency);
        $dir = storage_path('app/invoices');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = $data['invoice_number_file'].'.pdf';
        $path = $dir.DIRECTORY_SEPARATOR.$filename;

        $this->writePdf($data, $path);

        if (! is_file($path) || filesize($path) < 100) {
            return null;
        }

        return [
            'path' => $path,
            'filename' => $filename,
            'data' => $data,
        ];
    }

    public function buildData(
        User $user,
        WebUserSubscriptionModel $subscription,
        ?WebPlanModel $plan,
        float $paidTotal,
        string $currency = 'INR'
    ): array {
        $personal = WebPersonalDetails::with('stateRel')->where('user_id', $user->id)->first();
        $business = WebBusinessDetails::with(['cityRel', 'stateRel'])->where('user_id', $user->id)->first();

        $customerName = trim((string) ($user->name ?: (trim((optional($personal)->firstname ?? '').' '.(optional($personal)->lastname ?? '')))));
        $customerEmail = (string) ($user->email ?: (optional($personal)->email ?? ''));
        $customerPhone = (string) ($user->mobile ?: (optional($personal)->phone_number ?? (optional($business)->contactMobile ?? '')));
        $customerCompany = (string) (optional($business)->company_name ?? $user->companyname ?? '');
        $customerGst = (string) ($user->gst_no ?? '');
        $customerAddress = trim(implode(', ', array_filter([
            optional($business)->address ?? optional($personal)->address ?? $user->address ?? '',
            optional(optional($business)->cityRel)->city ?? optional($personal)->district ?? $user->city ?? '',
            optional(optional($business)->stateRel)->state ?? optional(optional($personal)->stateRel)->state ?? $user->state ?? '',
        ])));

        $periodLabel = $this->periodLabel((string) $subscription->subscription_type);
        $pricing = $this->planPricing($plan, (string) $subscription->subscription_type);

        $gstPercent = $pricing['gst_percent'];
        $taxable = $paidTotal;
        $gstAmount = 0.0;
        if ($gstPercent > 0 && $gstPercent <= 100) {
            $taxable = round($paidTotal / (1 + ($gstPercent / 100)), 2);
            $gstAmount = round($paidTotal - $taxable, 2);
        } elseif ($pricing['gst_amount'] > 0) {
            $gstAmount = $pricing['gst_amount'];
            $taxable = round(max($paidTotal - $gstAmount, 0), 2);
        }

        $invoiceNo = 'SNTC/INV/'.Carbon::now()->format('Y').'/'.str_pad((string) $subscription->id, 5, '0', STR_PAD_LEFT);

        return [
            'invoice_number' => $invoiceNo,
            'invoice_number_file' => 'SNTC-Invoice-'.$subscription->id.'-'.Carbon::now()->format('Ymd'),
            'invoice_date' => Carbon::now()->format('d M Y'),
            'seller_name' => config('invoice.seller_name'),
            'seller_address' => config('invoice.seller_address'),
            'seller_email' => config('invoice.seller_email'),
            'seller_phone' => config('invoice.seller_phone'),
            'seller_gstin' => config('invoice.seller_gstin'),
            'customer_name' => $customerName !== '' ? $customerName : 'Customer',
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'customer_company' => $customerCompany,
            'customer_gst' => $customerGst,
            'customer_address' => $customerAddress,
            'hsn_code' => (string) config('invoice.hsn_code', '998396'),
            'plan_title' => $plan->title ?? 'SNTC Web Plan',
            'period_label' => $periodLabel,
            'period_start' => $subscription->period_start
                ? Carbon::parse($subscription->period_start)->format('d M Y')
                : '',
            'period_end' => $subscription->period_end
                ? Carbon::parse($subscription->period_end)->format('d M Y')
                : '',
            'payment_id' => (string) $subscription->payment_id,
            'order_id' => (string) $subscription->order_id,
            'currency' => strtoupper($currency),
            'taxable' => $taxable,
            'gst_percent' => $gstPercent,
            'gst_amount' => $gstAmount,
            'total' => round($paidTotal, 2),
        ];
    }

    private function periodLabel(string $subscriptionType): string
    {
        return match ($subscriptionType) {
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'half_yearly' => 'Half yearly',
            'yearly' => 'Yearly',
            default => ucfirst(str_replace('_', ' ', $subscriptionType)),
        };
    }

    private function planPricing(?WebPlanModel $plan, string $subscriptionType): array
    {
        if ($plan === null) {
            return ['gst_percent' => 0.0, 'gst_amount' => 0.0];
        }

        $gst = 0.0;
        if ($subscriptionType === 'yearly') {
            $gst = (float) ($plan->yearly_gst ?? 0);
        } elseif (in_array($subscriptionType, ['quarterly', 'half_yearly'], true)) {
            $gst = (float) ($plan->quarterly_gst ?? 0);
        } else {
            $gst = (float) ($plan->monthly_gst ?? 0);
        }

        if ($gst > 0 && $gst <= 100) {
            return ['gst_percent' => $gst, 'gst_amount' => 0.0];
        }

        return ['gst_percent' => 0.0, 'gst_amount' => $gst];
    }

    private function writePdf(array $d, string $path): void
    {
        $pdf = new SimplePdf();
        $money = function (float $n) use ($d) {
            return $d['currency'].' '.number_format($n, 2);
        };

        $pdf->text(40, 800, $d['seller_name'], 16, true);
        $pdf->text(40, 782, 'TAX INVOICE', 13, true);
        $pdf->line(40, 772, 555, 772, 1);

        $pdf->text(40, 752, $d['seller_address'], 9);
        $pdf->text(40, 738, 'Email: '.$d['seller_email'], 9);
        if ($d['seller_phone'] !== '') {
            $pdf->text(40, 724, 'Phone: '.$d['seller_phone'], 9);
        }
        if ($d['seller_gstin'] !== '') {
            $pdf->text(40, 710, 'GSTIN: '.$d['seller_gstin'], 9);
        }

        $pdf->text(360, 752, 'Invoice No: '.$d['invoice_number'], 10, true);
        $pdf->text(360, 736, 'Date: '.$d['invoice_date'], 10);
        $pdf->text(360, 720, 'Payment ID: '.$d['payment_id'], 8);
        $pdf->text(360, 706, 'Order ID: '.$d['order_id'], 8);

        $pdf->text(40, 680, 'Bill To', 11, true);
        $pdf->text(40, 664, $d['customer_name'], 10, true);
        if ($d['customer_company'] !== '') {
            $pdf->text(40, 650, $d['customer_company'], 9);
        }
        $y = $d['customer_company'] !== '' ? 636 : 650;
        if ($d['customer_address'] !== '') {
            $pdf->text(40, $y, $d['customer_address'], 9);
            $y -= 14;
        }
        if ($d['customer_email'] !== '') {
            $pdf->text(40, $y, $d['customer_email'], 9);
            $y -= 14;
        }
        if ($d['customer_gst'] !== '') {
            $pdf->text(40, $y, 'GSTIN: '.$d['customer_gst'], 9);
        }

        $tableTop = 560;
        $pdf->rect(40, $tableTop - 6, 515, 24);
        $pdf->text(48, $tableTop, 'Description', 10, true);
        $pdf->text(250, $tableTop, 'HSN', 10, true);
        $pdf->text(330, $tableTop, 'Period', 10, true);
        $pdf->text(470, $tableTop, 'Amount', 10, true);

        $desc = $d['plan_title'].' ('.$d['period_label'].' subscription)';
        $pdf->text(48, $tableTop - 28, $desc, 9);
        $pdf->text(250, $tableTop - 28, $d['hsn_code'] ?: '998396', 9);
        $period = trim($d['period_start'].' - '.$d['period_end']);
        $pdf->text(330, $tableTop - 28, $period, 9);
        $pdf->text(470, $tableTop - 28, $money($d['taxable']), 9);

        $pdf->line(40, $tableTop - 42, 555, $tableTop - 42);

        $sumY = $tableTop - 70;
        $pdf->text(360, $sumY, 'Taxable value', 10);
        $pdf->text(470, $sumY, $money($d['taxable']), 10);
        $gstLabel = $d['gst_percent'] > 0 ? 'GST ('.$d['gst_percent'].'%)' : 'GST';
        $pdf->text(360, $sumY - 16, $gstLabel, 10);
        $pdf->text(470, $sumY - 16, $money($d['gst_amount']), 10);
        $pdf->line(360, $sumY - 24, 555, $sumY - 24, 1);
        $pdf->text(360, $sumY - 42, 'Total paid', 11, true);
        $pdf->text(470, $sumY - 42, $money($d['total']), 11, true);

        $pdf->text(40, 160, 'This is a computer-generated invoice and does not require a signature.', 8);
        $pdf->text(40, 146, 'Thank you for subscribing to SNTC.', 8);
        $pdf->line(40, 130, 555, 130);
        $pdf->text(40, 114, $d['seller_name'], 9, true);
        $pdf->text(40, 100, $d['seller_address'], 8);

        $pdf->save($path);
    }
}
