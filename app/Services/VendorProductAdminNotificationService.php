<?php

namespace App\Services;

use App\Http\Controllers\MailController;
use App\PackingType;
use App\User;
use App\WebBusinessDetails;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class VendorProductAdminNotificationService
{
    public const ACTION_CREATED = 'created';

    public const ACTION_UPDATED = 'updated';

    public const ACTION_VARIANTS_ADDED = 'variants_added';

    public const ACTION_ACCEPTED = 'accepted';

    private const ADMIN_MAIL = 'enquiry@sntcgroup.com';

    /**
     * @param  Collection<int, Model>|iterable<int, Model>  $variants
     */
    public static function notify(
        string $kind,
        string $action,
        Model $product,
        $variants,
        ?string $typeLabel = null
    ): void {
        try {
            (new self())->send($kind, $action, $product, $variants, $typeLabel);
        } catch (\Throwable $e) {
            Log::warning('Vendor product admin mail failed: '.$e->getMessage());
        }
    }

    /**
     * Email the vendor when admin verifies/accepts their product (status 0 → 1).
     */
    public static function notifyAccepted(string $kind, Model $product, ?string $typeLabel = null): void
    {
        try {
            (new self())->sendAccepted($kind, $product, $typeLabel);
        } catch (\Throwable $e) {
            Log::warning('Vendor product accepted mail failed: '.$e->getMessage());
        }
    }

    /**
     * Email the vendor when admin de-activates their product (status 1 → 0).
     */
    public static function notifyDeactivated(string $kind, Model $product, string $reason, ?string $typeLabel = null): void
    {
        try {
            (new self())->sendDeactivated($kind, $product, $reason, $typeLabel);
        } catch (\Throwable $e) {
            Log::warning('Vendor product deactivated mail failed: '.$e->getMessage());
        }
    }

    /**
     * @param  Collection<int, mixed>|iterable<int, mixed>  $previous
     * @param  Collection<int, mixed>|iterable<int, mixed>  $current
     */
    public static function hasNewPackingVariants($previous, $current): bool
    {
        return self::hasNewIdentities(
            self::packingIdentitySet($previous),
            self::packingIdentitySet($current)
        );
    }

    /**
     * @param  Collection<int, mixed>|iterable<int, mixed>  $previous
     * @param  Collection<int, mixed>|iterable<int, mixed>  $current
     */
    public static function hasNewEquipmentVariants($previous, $current): bool
    {
        return self::hasNewIdentities(
            self::equipmentIdentitySet($previous),
            self::equipmentIdentitySet($current)
        );
    }

    /**
     * @param  Collection<int, Model>|iterable<int, Model>  $variants
     */
    private function send(
        string $kind,
        string $action,
        Model $product,
        $variants,
        ?string $typeLabel
    ): void {
        $variantRows = collect($variants)->values();
        // Create / new-variants mails need at least one variant; updates may be product-only.
        if ($variantRows->isEmpty() && $action !== self::ACTION_UPDATED) {
            return;
        }

        $meta = $this->kindMeta($kind);
        $userId = (int) ($product->user_id ?? 0);
        $user = $userId > 0 ? User::query()->find($userId, ['id', 'name', 'email', 'mobile', 'phone', 'companyname']) : null;
        $business = $userId > 0
            ? WebBusinessDetails::query()->where('user_id', $userId)->first(['company_name', 'registered_email', 'contactMobile', 'phone'])
            : null;

        $isCreate = $action === self::ACTION_CREATED;
        $isUpdate = $action === self::ACTION_UPDATED;
        $isVariantsAdded = $action === self::ACTION_VARIANTS_ADDED;
        $productLabel = $meta['label'];

        $mailData = [
            'isCreate' => $isCreate,
            'isUpdate' => $isUpdate,
            'isVariantsAdded' => $isVariantsAdded,
            'productKind' => $productLabel,
            'productId' => (int) $product->id,
            'typeLabel' => $typeLabel ?: $this->resolveTypeLabel($kind, $product),
            'specification' => $product->specification ?? null,
            'description' => $product->description ?? null,
            'statusLabel' => ((int) ($product->status ?? 0) === 1) ? 'Active' : 'Pending',
            'variantCount' => $variantRows->count(),
            'variants' => $this->formatVariants($kind, $variantRows),
            'reviewUrl' => $this->reviewUrl($meta['showRoute'], (int) $product->id),
            'userId' => $userId > 0 ? $userId : '—',
            'userName' => $user->name ?? null,
            'userEmail' => $user->email ?? ($business->registered_email ?? null),
            'userMobile' => $user->mobile ?? ($user->phone ?? ($business->contactMobile ?? null)),
            'companyName' => $business->company_name ?? ($user->companyname ?? null),
            'submittedAt' => Carbon::now()->timezone('Asia/Kolkata')->format('d-m-Y, g:i A'),
        ];

        if ($isCreate) {
            $subject = 'New '.$productLabel.' product submitted – #'.$product->id;
        } elseif ($isVariantsAdded) {
            $subject = 'New '.$productLabel.' variants added – #'.$product->id;
        } else {
            $subject = $productLabel.' product updated – #'.$product->id;
        }

        MailController::sendVendorProductVariantsMail(
            self::ADMIN_MAIL,
            self::ADMIN_MAIL,
            'SNTC',
            $subject,
            $mailData
        );
    }

    private function sendAccepted(string $kind, Model $product, ?string $typeLabel): void
    {
        $meta = $this->kindMeta($kind);
        $userId = (int) ($product->user_id ?? 0);
        $user = $userId > 0 ? User::query()->find($userId, ['id', 'name', 'email', 'mobile', 'phone', 'companyname']) : null;
        $business = $userId > 0
            ? WebBusinessDetails::query()->where('user_id', $userId)->first(['company_name', 'registered_email', 'contactMobile', 'phone'])
            : null;

        $mailTo = $user->email ?? ($business->registered_email ?? null);
        if (! is_string($mailTo) || trim($mailTo) === '') {
            Log::warning('Vendor product accepted mail skipped: no recipient email for product #'.(int) $product->id);

            return;
        }

        $productLabel = $meta['label'];
        $mailData = [
            'productKind' => $productLabel,
            'productId' => (int) $product->id,
            'typeLabel' => $typeLabel ?: $this->resolveTypeLabel($kind, $product),
            'specification' => $product->specification ?? null,
            'description' => $product->description ?? null,
            'statusLabel' => 'Active',
            'userId' => $userId > 0 ? $userId : '—',
            'userName' => $user->name ?? ($business->company_name ?? 'Vendor'),
            'userEmail' => $mailTo,
            'companyName' => $business->company_name ?? ($user->companyname ?? null),
            'acceptedAt' => Carbon::now()->timezone('Asia/Kolkata')->format('d-m-Y, g:i A'),
        ];

        $subject = 'Your '.$productLabel.' product has been accepted – #'.$product->id;

        MailController::sendVendorProductAcceptedMail(
            $mailTo,
            self::ADMIN_MAIL,
            'SNTC',
            $subject,
            $mailData
        );
    }

    private function sendDeactivated(string $kind, Model $product, string $reason, ?string $typeLabel): void
    {
        $meta = $this->kindMeta($kind);
        $userId = (int) ($product->user_id ?? 0);
        $user = $userId > 0 ? User::query()->find($userId, ['id', 'name', 'email', 'mobile', 'phone', 'companyname']) : null;
        $business = $userId > 0
            ? WebBusinessDetails::query()->where('user_id', $userId)->first(['company_name', 'registered_email', 'contactMobile', 'phone'])
            : null;

        $mailTo = $user->email ?? ($business->registered_email ?? null);
        if (! is_string($mailTo) || trim($mailTo) === '') {
            Log::warning('Vendor product deactivated mail skipped: no recipient email for product #'.(int) $product->id);

            return;
        }

        $productLabel = $meta['label'];
        $mailData = [
            'productKind' => $productLabel,
            'productId' => (int) $product->id,
            'typeLabel' => $typeLabel ?: $this->resolveTypeLabel($kind, $product),
            'reason' => trim($reason),
            'userId' => $userId > 0 ? $userId : '—',
            'userName' => $user->name ?? ($business->company_name ?? 'Vendor'),
            'userEmail' => $mailTo,
            'companyName' => $business->company_name ?? ($user->companyname ?? null),
            'deactivatedAt' => Carbon::now()->timezone('Asia/Kolkata')->format('d-m-Y, g:i A'),
        ];

        $subject = 'Your '.$productLabel.' product has been de-activated – #'.$product->id;

        MailController::sendVendorProductDeactivatedMail(
            $mailTo,
            self::ADMIN_MAIL,
            'SNTC',
            $subject,
            $mailData
        );
    }

    private function kindMeta(string $kind): array
    {
        return match ($kind) {
            'rice_bag' => [
                'label' => 'Rice bag',
                'showRoute' => 'get.web.rice.bag.products.show',
            ],
            'cartoon' => [
                'label' => 'Cartoon',
                'showRoute' => 'get.web.cartoon.products.show',
            ],
            'cylinder' => [
                'label' => 'Cylinder',
                'showRoute' => 'get.web.cylinder.products.show',
            ],
            'lab_equipment' => [
                'label' => 'Lab equipment',
                'showRoute' => 'get.web.lab.equipment.products.show',
            ],
            'machinery_equipment' => [
                'label' => 'Machinery equipment',
                'showRoute' => 'get.web.machinery.equipment.products.show',
            ],
            default => [
                'label' => 'Vendor',
                'showRoute' => null,
            ],
        };
    }

    private function resolveTypeLabel(string $kind, Model $product): string
    {
        $label = '—';

        if ($kind === 'rice_bag' && ! empty($product->bag_type_id)) {
            $label = \App\VendorPackingType::query()->where('id', $product->bag_type_id)->value('name')
                ?: PackingType::query()->where('id', $product->bag_type_id)->value('name')
                ?: '—';
        } elseif ($kind === 'cartoon' && ! empty($product->cartoon_type_id)) {
            $label = \App\CartoonType::query()->where('id', $product->cartoon_type_id)->value('type') ?: '—';
        } elseif ($kind === 'cylinder' && ! empty($product->cylinder_type_id)) {
            $label = \App\CylinderType::query()->where('id', $product->cylinder_type_id)->value('type') ?: '—';
        } else {
            $label = $product->packing_form ?: '—';
        }

        if (! empty($product->other_type_value)) {
            $label .= ' ('.$product->other_type_value.')';
        }

        return $label;
    }

    /**
     * @param  Collection<int, Model>  $variants
     * @return list<string>
     */
    private function formatVariants(string $kind, Collection $variants): array
    {
        if (in_array($kind, ['lab_equipment', 'machinery_equipment'], true)) {
            return $variants->map(function (Model $variant) {
                $parts = array_filter([
                    $variant->equipment_name ?: ('Equipment #'.($variant->equipment_id ?? '—')),
                    $variant->rate !== null ? 'Rate: '.$variant->rate : null,
                    $variant->description ? 'Desc: '.$variant->description : null,
                ]);

                return implode(' | ', $parts);
            })->all();
        }

        return $variants->map(function (Model $variant) {
            $sizeLabel = $variant->packing_size ?: ($variant->packing_size_id ? 'Size #'.$variant->packing_size_id : null);
            if (! empty($variant->other_size_value)) {
                $sizeLabel = trim(($sizeLabel ?: 'Other').' ('.$variant->other_size_value.')');
            }

            $parts = array_filter([
                $sizeLabel,
                $variant->bag_size ? 'Bag: '.$variant->bag_size : null,
                $variant->bag_weight ? 'Weight: '.$variant->bag_weight : null,
                $variant->rate !== null ? 'Rate: '.$variant->rate : null,
                $variant->gst !== null ? 'GST: '.$variant->gst : null,
                $variant->total_price !== null ? 'Total: '.$variant->total_price : null,
            ]);

            return implode(' | ', $parts) ?: 'Variant #'.$variant->id;
        })->all();
    }

    private function reviewUrl(?string $routeName, int $productId): ?string
    {
        if (! $routeName) {
            return null;
        }

        try {
            return route($routeName, $productId);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param  array<string, true>  $previous
     * @param  array<string, true>  $current
     */
    private static function hasNewIdentities(array $previous, array $current): bool
    {
        if ($current === []) {
            return false;
        }

        foreach ($current as $key => $_) {
            if (! isset($previous[$key])) {
                return true;
            }
        }

        return count($current) > count($previous);
    }

    /**
     * @param  Collection<int, mixed>|iterable<int, mixed>  $rows
     * @return array<string, true>
     */
    private static function packingIdentitySet($rows): array
    {
        $set = [];
        foreach ($rows as $row) {
            $packingSizeId = self::valueOf($row, 'packing_size_id', 'packingSizeId');
            $packingSize = self::valueOf($row, 'packing_size', 'packingSize');
            if ($packingSizeId !== null && $packingSizeId !== '') {
                $set['ps:'.(int) $packingSizeId] = true;
            } elseif (is_string($packingSize) && trim($packingSize) !== '') {
                $set['sz:'.strtolower(trim($packingSize))] = true;
            }
        }

        return $set;
    }

    /**
     * @param  Collection<int, mixed>|iterable<int, mixed>  $rows
     * @return array<string, true>
     */
    private static function equipmentIdentitySet($rows): array
    {
        $set = [];
        foreach ($rows as $row) {
            $equipmentId = self::valueOf($row, 'equipment_id', 'equipmentId');
            if ($equipmentId !== null && $equipmentId !== '') {
                $set['eq:'.(int) $equipmentId] = true;
            }
        }

        return $set;
    }

    private static function valueOf($row, string $snake, string $camel)
    {
        if (is_array($row)) {
            return $row[$snake] ?? $row[$camel] ?? null;
        }

        if (is_object($row)) {
            return $row->{$snake} ?? $row->{$camel} ?? null;
        }

        return null;
    }
}
