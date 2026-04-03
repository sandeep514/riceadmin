<?php

namespace App;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class WebUserAttachment extends Model
{
    protected $table = 'web_user_attachment';

    protected $fillable = ['user_id', 'panCard', 'farmer_file', 'gst_fssai', 'gstCard', 'fssaiCard', 'status'];

    /**
     * Legacy columns; API/JSON uses a single gst_fssai path (e.g. gst/file.pdf, gst_fssai/file.pdf).
     */
    protected $hidden = ['gstCard', 'fssaiCard'];

    /**
     * Split path so clients can build URLs without encodeURIComponent on the full string (avoids %2F breaking static files).
     * Use: {prefix}/ + gst_fssai_folder + '/' + gst_fssai_file  (e.g. webPortal/1/attachments/gst_fssai/screenshot.png).
     */
    protected $appends = ['gst_fssai_folder', 'gst_fssai_file'];

    protected function gstFssai(): Attribute
    {
        return Attribute::make(
            get: function (?string $value, array $attributes) {
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
        );
    }

    protected function gstFssaiFolder(): Attribute
    {
        return Attribute::get(function () {
            $path = $this->gst_fssai;
            if ($path === null || $path === '') {
                return null;
            }
            if (str_contains($path, '/')) {
                return dirname($path);
            }

            return 'gst_fssai';
        });
    }

    protected function gstFssaiFile(): Attribute
    {
        return Attribute::get(function () {
            $path = $this->gst_fssai;
            if ($path === null || $path === '') {
                return null;
            }
            if (str_contains($path, '/')) {
                return basename($path);
            }

            return $path;
        });
    }

    /**
     * Trial onboarding: PAN + one GST-or-FSSAI document + farmer file.
     */
    public function trialDocumentsComplete(): bool
    {
        $gst = $this->gst_fssai;

        return ! empty($this->panCard)
            && $gst !== null && $gst !== ''
            && ! empty($this->farmer_file);
    }
}
