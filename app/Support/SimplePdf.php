<?php

namespace App\Support;

/**
 * Minimal PDF 1.4 writer (Helvetica) for one-page invoices.
 */
class SimplePdf
{
    private array $ops = [];

    private int $pageW = 595;

    private int $pageH = 842;

    public function text(float $x, float $y, string $text, int $size = 11, bool $bold = false): void
    {
        $font = $bold ? 'F2' : 'F1';
        $safe = $this->escape($text);
        $this->ops[] = sprintf(
            'BT /%s %d Tf 1 0 0 1 %.2f %.2f Tm (%s) Tj ET',
            $font,
            $size,
            $x,
            $y,
            $safe
        );
    }

    public function line(float $x1, float $y1, float $x2, float $y2, float $width = 0.6): void
    {
        $this->ops[] = sprintf('%.2f w %.2f %.2f m %.2f %.2f l S', $width, $x1, $y1, $x2, $y2);
    }

    public function rect(float $x, float $y, float $w, float $h, bool $fill = false): void
    {
        $this->ops[] = sprintf('%.2f %.2f %.2f %.2f re %s', $x, $y, $w, $h, $fill ? 'f' : 'S');
    }

    public function save(string $path): void
    {
        $stream = implode("\n", $this->ops);
        $len = strlen($stream);

        $objs = [];
        $objs[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objs[] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
        $objs[] = sprintf(
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %d %d] /Contents 4 0 R /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> >>',
            $this->pageW,
            $this->pageH
        );
        $objs[] = "<< /Length {$len} >>\nstream\n{$stream}\nendstream";
        $objs[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objs[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';

        $out = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objs as $i => $body) {
            $offsets[] = strlen($out);
            $out .= ($i + 1)." 0 obj\n".$body."\nendobj\n";
        }
        $xref = strlen($out);
        $out .= 'xref\n0 '. (count($objs) + 1) ."\n";
        $out .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objs); $i++) {
            $out .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $out .= 'trailer << /Size '.(count($objs) + 1).' /Root 1 0 R >>\n';
        $out .= "startxref\n{$xref}\n%%EOF";

        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($path, $out);
    }

    private function escape(string $text): string
    {
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        if ($converted === false) {
            $converted = preg_replace('/[^\x20-\x7E]/', '?', $text) ?? $text;
        }

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $converted);
    }
}
