<?php

namespace App\Http\Controllers;

use App\Role;
use App\Services\PaymentInvoiceService;
use App\WebPlanModel;
use App\WebUserSubscriptionModel;
use Illuminate\Support\Facades\Schema;

class WebPaymentController extends Controller
{
    public function index()
    {
        $query = WebUserSubscriptionModel::with([
            'userRel',
            'planRel.roleRel',
            'planRel.categoryRel',
        ])
            ->orderByDesc('id');

        $query->where(function ($q) {
            $q->whereNull('subscription_type')
                ->orWhere('subscription_type', '!=', 'trial');
        });

        $payments = $query->get();

        $roleNames = Role::pluck('role_name', 'id');

        return view('web-payments.index', compact('payments', 'roleNames'));
    }

    public function downloadInvoice($id)
    {
        $payment = WebUserSubscriptionModel::with(['userRel', 'planRel'])->find($id);
        if ($payment === null) {
            abort(404);
        }

        $path = $payment->invoiceAbsolutePath();
        if ($path === null) {
            $user = $payment->userRel;
            if ($user) {
                $amount = $payment->displayAmount() ?? 0;
                $currency = $payment->currency ?: 'INR';
                $made = (new PaymentInvoiceService())->makePdf(
                    $user,
                    $payment,
                    $payment->planRel,
                    (float) $amount,
                    $currency
                );
                if ($made && ! empty($made['path']) && is_file($made['path'])) {
                    $path = $made['path'];
                    if (Schema::hasColumn('web_user_subscription', 'invoice_path')) {
                        $payment->invoice_path = 'invoices/'.$made['filename'];
                        $payment->save();
                    }
                }
            }
        }

        if ($path === null || ! is_file($path)) {
            abort(404, 'Invoice not found.');
        }

        return response()->download($path, basename($path), [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
