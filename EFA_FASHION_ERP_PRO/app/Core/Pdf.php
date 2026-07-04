<?php

namespace App\Core;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Thin wrapper around Dompdf that renders a view to a PDF and streams it.
 */
class Pdf
{
    /** Render a view to PDF HTML and stream it to the browser for download/inline view. */
    public static function stream(string $view, array $data, string $filename, bool $inline = true): void
    {
        $html = View::partial($view, $data);
        $dompdf = self::make($html);
        $dompdf->stream($filename, ['Attachment' => $inline ? 0 : 1]);
        exit;
    }

    /** Return raw PDF bytes (useful for attaching/saving). */
    public static function output(string $view, array $data): string
    {
        $html = View::partial($view, $data);
        return self::make($html)->output();
    }

    private static function make(string $html): Dompdf
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);       // allow CDN/remote assets
        $options->set('defaultFont', 'DejaVu Sans');  // unicode coverage
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf;
    }
}
