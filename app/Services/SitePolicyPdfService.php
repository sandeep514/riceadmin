<?php

namespace App\Services;

use App\SitePolicy;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SitePolicyPdfService
{
    public const UPLOAD_DIR = 'uploads/site-policies';

    /**
     * Generate (or regenerate) PDF for a policy and persist pdf_path on the model.
     */
    public function generateAndStore(SitePolicy $policy): ?string
    {
        $dir = public_path(self::UPLOAD_DIR);
        if (! is_dir($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $safeSlug = Str::slug($policy->slug ?: 'policy') ?: 'policy';
        $relativePath = self::UPLOAD_DIR.'/'.$safeSlug.'.pdf';
        $absolutePath = public_path($relativePath);

        try {
            $pdf = Pdf::loadView('sitePolicies.pdf', [
                'policy' => $policy,
                'generatedAt' => now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('d-m-Y H:i'),
            ])->setPaper('a4');

            $pdf->save($absolutePath);

            $policy->update(['pdf_path' => $relativePath]);

            return $relativePath;
        } catch (\Throwable $e) {
            Log::error('Site policy PDF generation failed: '.$e->getMessage(), [
                'policy_id' => $policy->id,
                'slug' => $policy->slug,
            ]);

            return null;
        }
    }

    public function absolutePathForTerms(): ?string
    {
        $policy = SitePolicy::termsAndConditions();
        if (! $policy) {
            return null;
        }

        if ($policy->absolute_pdf_path) {
            return $policy->absolute_pdf_path;
        }

        $this->generateAndStore($policy);

        return $policy->fresh()?->absolute_pdf_path;
    }
}
