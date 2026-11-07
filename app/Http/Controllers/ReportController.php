<?php

namespace App\Http\Controllers;

use App\Models\CalculationRun;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): Response
    {
        $approvedRuns = CalculationRun::with(['estate', 'landBlock', 'result.poolResults.carbonPool', 'methodologyVersion.methodology'])
            ->approved()
            ->latest('period_end')
            ->get();

        return Inertia::render('reports/Index', [
            'approvedRuns' => $approvedRuns,
            'totalRuns' => CalculationRun::count(),
            'approvedCount' => $approvedRuns->count(),
        ]);
    }

    public function downloadCsv(Request $request): StreamedResponse
    {
        $runs = CalculationRun::with(['estate', 'landBlock', 'result', 'methodologyVersion.methodology'])
            ->approved()
            ->latest('period_end')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="carbon_stock_sequestration_report_'.now()->format('Ymd_His').'.csv"',
        ];

        return response()->stream(function () use ($runs) {
            $handle = fopen('php://output', 'w');

            // Header kolom
            fputcsv($handle, [
                'ID Kalkulasi',
                'Nama Estate',
                'Kode Blok',
                'Nama Blok',
                'Tipe Ekosistem',
                'Luas (ha)',
                'Periode Awal',
                'Periode Akhir',
                'Metodologi',
                'Total Biomassa (ton)',
                'Total Karbon (ton C)',
                'Total CO2e (t CO2e)',
                'Kerapatan Stok Karbon (t C/ha)',
                'Kerapatan Stok CO2e (t CO2e/ha)',
                'Laju Sekuestrasi (t CO2e/tahun)',
                'Status Audit',
                'Tanggal Kalkulasi',
            ]);

            foreach ($runs as $r) {
                fputcsv($handle, [
                    $r->id,
                    $r->estate->name ?? '',
                    $r->landBlock->code ?? '',
                    $r->landBlock->name ?? '',
                    $r->ecosystem_type,
                    $r->area_ha,
                    $r->period_start->toDateString(),
                    $r->period_end->toDateString(),
                    $r->methodologyVersion->methodology->name ?? '',
                    $r->result->total_biomass_t ?? 0,
                    $r->result->total_carbon_t ?? 0,
                    $r->result->total_co2e_t ?? 0,
                    $r->result->carbon_stock_t_ha ?? 0,
                    $r->result->co2e_stock_t_ha ?? 0,
                    $r->result->sequestration_t_co2e_year ?? 'N/A',
                    $r->status,
                    $r->calculated_at?->toDateTimeString() ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
