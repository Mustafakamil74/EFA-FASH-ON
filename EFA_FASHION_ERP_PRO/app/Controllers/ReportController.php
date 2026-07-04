<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Pdf;
use App\Models\Report;
use App\Models\Accounting;
use App\Models\Setting;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function index(): void
    {
        $this->authorize('reports.view');
        [$from, $to, $preset] = $this->range();
        $year = (int) substr($to, 0, 4);

        $this->view('reports.index', [
            'title'        => __('nav_reports'),
            'from'         => $from,
            'to'           => $to,
            'preset'       => $preset,
            'salesByDay'   => Report::salesByDay($from, $to),
            'monthly'      => Report::monthly($year),
            'topCustomers' => Report::topCustomers($from, $to),
            'topProducts'  => Report::topProducts($from, $to),
            'invByCat'     => Report::inventoryByCategory(),
            'pl'           => Accounting::profitAndLoss($from, $to),
            'year'         => $year,
        ]);
    }

    public function exportPdf(): void
    {
        $this->authorize('reports.view');
        [$from, $to] = $this->range();
        Pdf::stream('reports.sales_pdf', [
            'rows'    => Report::salesRows($from, $to),
            'pl'      => Accounting::profitAndLoss($from, $to),
            'from'    => $from,
            'to'      => $to,
            'company' => Setting::all(),
        ], 'sales-report-' . $from . '_' . $to . '.pdf', false);
    }

    public function exportExcel(): void
    {
        $this->authorize('reports.view');
        [$from, $to] = $this->range();
        $rows = Report::salesRows($from, $to);

        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('Sales');
        $sheet->fromArray(['Number', 'Date', 'Party', 'Currency', 'Total'], null, 'A1');
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $r = 2;
        foreach ($rows as $row) {
            $sheet->fromArray([
                $row['number'], $row['receipt_date'], $row['party_name'] ?? '',
                $row['currency'], (float) $row['grand_total'],
            ], null, 'A' . $r);
            $r++;
        }
        $sheet->setCellValue('D' . $r, 'Total');
        $sheet->setCellValue('E' . $r, '=SUM(E2:E' . ($r - 1) . ')');
        $sheet->getStyle('D' . $r . ':E' . $r)->getFont()->setBold(true);
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'sales-report-' . $from . '_' . $to . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($book))->save('php://output');
        exit;
    }

    /** Resolve [from, to, preset] from the request (presets or custom). */
    private function range(): array
    {
        $preset = (string) $this->input('preset', 'monthly');
        $today  = date('Y-m-d');
        switch ($preset) {
            case 'daily':
                return [$today, $today, $preset];
            case 'weekly':
                return [date('Y-m-d', strtotime('-6 days')), $today, $preset];
            case 'yearly':
                return [date('Y-01-01'), date('Y-12-31'), $preset];
            case 'custom':
                $from = (string) $this->input('from', date('Y-m-01'));
                $to   = (string) $this->input('to', $today);
                return [$from, $to, $preset];
            case 'monthly':
            default:
                return [date('Y-m-01'), $today, 'monthly'];
        }
    }
}
