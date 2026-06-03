<?php

namespace App;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class WebUserAttachment extends Model
{
    protected $table = 'web_user_attachment';

    protected $fillable = ['user_id', 'panCard', 'farmer_file', 'gst_fssai', 'gstCard', 'fssaiCard', 'status'];

    /**
     * Legacy columns; API/JSON uses a single gst/fssai document.
     */
    protected $hidden = ['gstCard', 'fssaiCard'];

    /**
     * Subfolder under attachments/ (gst_fssai, gst, or fssai). Combine with prefix.gst_fssai or prefix.attachments.
     */
    protected $appends = ['gst_fssai_folder'];

    /**
     * Full relative path under attachments/ (e.g. gst_fssai/file.png, gst/old.pdf). Not exposed in JSON.
     */
    public function resolveGstFssaiRelativePath(): ?string
    {
        return self::resolveGstFssaiRelativePathFrom($this->getAttributes());
    }

    private static function resolveGstFssaiRelativePathFrom(array $attributes): ?string
    {
        if (array_key_exists('gst_fssai', $attributes) && $attributes['gst_fssai'] !== null && $attributes['gst_fssai'] !== '') {
            return $attributes['gst_fssai'];
        }
        if (! empty($attributes['gstCard'])) {
            return 'gst/' . ltrim($attributes['gstCard'], '/');
        }
        if (! empty($attributes['fssaiCard'])) {
            return 'fssai/' . ltrim($attributes['fssaiCard'], '/');
        }

        return null;
    }

    /**
     * API/json: filename only (no folder prefix). URL: prefix.gst_fssai + '/' + gst_fssai when gst_fssai_folder is gst_fssai.
     */
    protected function gstFssai(): Attribute
    {
        return Attribute::make(
            get: function (?string $value, array $attributes) {
                $full = self::resolveGstFssaiRelativePathFrom($attributes);
                if ($full === null || $full === '') {
                    return null;
                }

                return basename($full);
            }
        );
    }

    protected function gstFssaiFolder(): Attribute
    {
        return Attribute::get(function () {
            $full = $this->resolveGstFssaiRelativePath();
            if ($full === null || $full === '') {
                return null;
            }
            if (str_contains($full, '/')) {
                return dirname($full);
            }

            return 'gst_fssai';
        });
    }

    /**
     * Trial onboarding: PAN + one GST-or-FSSAI document + farmer file.
     */
    public function trialDocumentsComplete(): bool
    {
        $full = $this->resolveGstFssaiRelativePath();

        return ! empty($this->panCard)
            && $full !== null && $full !== ''
            && ! empty($this->farmer_file);
    }

    /**
     * At least one of PAN, farmer file, or GST/FSSAI has been uploaded.
     */
    public function hasAnyTrialDocumentUploaded(): bool
    {
        $full = $this->resolveGstFssaiRelativePath();

        return ! empty($this->panCard)
            || ! empty($this->farmer_file)
            || ($full !== null && $full !== '');
    }
}
