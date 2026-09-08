<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SitePolicy extends Model
{
    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    public const TYPE_TERMS = 'terms_and_conditions';

    public const TYPE_PRIVACY = 'privacy_policy';

    public const TYPE_DISCLAIMER = 'disclaimer';

    public const TYPE_RETURN = 'return_policy';

    protected $fillable = [
        'slug',
        'title',
        'content',
        'pdf_path',
        'status',
        'order_no',
    ];

    protected $casts = [
        'status' => 'integer',
        'order_no' => 'integer',
    ];

    public static function predefinedTypes(): array
    {
        return [
            self::TYPE_TERMS => 'Terms and Conditions',
            self::TYPE_PRIVACY => 'Privacy Policy',
            self::TYPE_DISCLAIMER => 'Disclaimer',
            self::TYPE_RETURN => 'Return Policy',
        ];
    }

    public function getAbsolutePdfPathAttribute(): ?string
    {
        if (! $this->pdf_path) {
            return null;
        }

        $path = public_path(ltrim($this->pdf_path, '/'));

        return is_file($path) ? $path : null;
    }

    public function getPdfUrlAttribute(): ?string
    {
        return $this->pdf_path ? asset($this->pdf_path) : null;
    }

    public static function termsAndConditions(): ?self
    {
        return static::query()
            ->where('slug', self::TYPE_TERMS)
            ->where('status', self::STATUS_ACTIVE)
            ->first();
    }
}
