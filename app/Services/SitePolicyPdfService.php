<?php

namespace App\Services;

use App\SitePolicy;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SitePolicyPdfService
{
    public const UPLOAD_DIR = 'uploads/site-policies';

    /**
     * Generate (or regenerate) PDF for a policy and persist pdf_path on the model.
     */
    public function generateAndStore(SitePolicy $policy): ?string
    {
        $binary = $this->renderBinary($policy);
        if ($binary === null) {
            return null;
        }

        $dir = public_path(self::UPLOAD_DIR);
        if (! is_dir($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        if (! is_dir($dir) || ! is_writable($dir)) {
            Log::error('Site policy PDF directory is not writable.', [
                'policy_id' => $policy->id,
                'dir' => $dir,
            ]);

            return null;
        }

        $relativePath = self::UPLOAD_DIR.'/'.$this->fileNameForSlug((string) $policy->slug);
        $absolutePath = public_path($relativePath);

        try {
            // Write via File::put (absolute path). Avoid Pdf::save() — it may route to a Storage disk.
            if (File::put($absolutePath, $binary) === false) {
                Log::error('Site policy PDF write failed.', [
                    'policy_id' => $policy->id,
                    'path' => $absolutePath,
                ]);

                return null;
            }

            @chmod($absolutePath, 0644);

            $policy->forceFill(['pdf_path' => $relativePath])->save();

            return $relativePath;
        } catch (\Throwable $e) {
            Log::error('Site policy PDF store failed: '.$e->getMessage(), [
                'policy_id' => $policy->id,
                'slug' => $policy->slug,
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Absolute filesystem path to the Terms & Conditions PDF (regenerates if missing).
     */
    public function absolutePathForTerms(): ?string
    {
        $policy = SitePolicy::termsAndConditions();
        if (! $policy) {
            Log::warning('Welcome mail: no active terms_and_conditions policy found.');

            return null;
        }

        if ($policy->absolute_pdf_path) {
            return $policy->absolute_pdf_path;
        }

        $this->generateAndStore($policy);

        return $policy->fresh()?->absolute_pdf_path;
    }

    /**
     * Raw PDF bytes for Terms & Conditions (for mail attachData fallback).
     */
    public function termsPdfBinary(): ?string
    {
        $policy = SitePolicy::termsAndConditions();
        if (! $policy) {
            return null;
        }

        $path = $this->absolutePathForTerms();
        if ($path && is_file($path)) {
            $bytes = @file_get_contents($path);
            if (is_string($bytes) && $bytes !== '') {
                return $bytes;
            }
        }

        return $this->renderBinary($policy);
    }

    /**
     * @return string|null PDF binary
     */
    public function renderBinary(SitePolicy $policy): ?string
    {
        $this->ensureDompdfFontsDirectory();

        try {
            $pdf = Pdf::loadView('sitePolicies.pdf', [
                'policy' => $policy,
                'contentHtml' => $this->sanitizeContentForPdf((string) ($policy->content ?? '')),
                'generatedAt' => now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('d-m-Y H:i'),
            ])->setPaper('a4');

            $binary = $pdf->output();
            if (! is_string($binary) || $binary === '') {
                Log::error('Site policy PDF output was empty.', [
                    'policy_id' => $policy->id,
                    'slug' => $policy->slug,
                ]);

                return null;
            }

            return $binary;
        } catch (\Throwable $e) {
            Log::error('Site policy PDF generation failed: '.$e->getMessage(), [
                'policy_id' => $policy->id,
                'slug' => $policy->slug,
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    private function fileNameForSlug(string $slug): string
    {
        $safe = strtolower(trim($slug));
        $safe = preg_replace('/[^a-z0-9_\-]+/', '', str_replace(' ', '_', $safe)) ?: 'policy';

        return $safe.'.pdf';
    }

    /**
     * DomPDF-safe HTML: keep basic formatting from the rich text editor.
     */
    private function sanitizeContentForPdf(string $content): string
    {
        $content = trim($content);
        if ($content === '') {
            return '<p></p>';
        }

        // Plain text → preserve line breaks
        if ($content === strip_tags($content)) {
            return nl2br(e($content));
        }

        $allowed = '<p><br><br/><b><strong><i><em><u><ul><ol><li><h1><h2><h3><h4><blockquote><a><span><div>';
        $clean = strip_tags($content, $allowed);

        // Drop inline event handlers / javascript URLs
        $clean = preg_replace('/\son\w+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', $clean) ?? $clean;
        $clean = preg_replace('/href\s*=\s*([\'"])\s*javascript:[^\'"]*\1/i', 'href="#"', $clean) ?? $clean;

        // DomPDF struggles with empty tags sometimes
        $clean = preg_replace('/<p>\s*<\/p>/i', '<p>&nbsp;</p>', $clean) ?? $clean;

        return $clean;
    }

    private function ensureDompdfFontsDirectory(): void
    {
        $fonts = storage_path('fonts');
        if (! is_dir($fonts)) {
            File::makeDirectory($fonts, 0755, true);
        }
    }
}
