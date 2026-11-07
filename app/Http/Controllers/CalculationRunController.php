<?php

namespace App\Http\Controllers;

use App\DTOs\Calculation\CalculationInputDTO;
use App\Http\Requests\Calculation\CalculationRunRequest;
use App\Models\CalculationComparison;
use App\Models\CalculationResult;
use App\Models\CalculationRun;
use App\Models\CarbonPool;
use App\Models\CarbonPoolResult;
use App\Models\Estate;
use App\Models\LandBlock;
use App\Models\MethodologyVersion;
use App\Services\Audit\AuditLogger;
use App\Services\Calculation\CarbonCalculationEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CalculationRunController extends Controller
{
    public function __construct(
        protected CarbonCalculationEngine $calculationEngine
    ) {}

    public function index(Request $request): Response
    {
        $runs = CalculationRun::with(['landBlock', 'estate', 'methodologyVersion.methodology', 'result'])
            ->filter($request->only(['status', 'ecosystem_type', 'estate_id']))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('calculations/Index', [
            'runs' => $runs,
            'estates' => Estate::select('id', 'name', 'code')->get(),
            'filters' => $request->only(['status', 'ecosystem_type', 'estate_id']),
        ]);
    }

    public function create(): Response
    {
        $blocks = LandBlock::with([
            'estate',
            'cropCycles.palmStands',
            'fieldMeasurements.palmMeasurements',
            'fieldMeasurements.treeMeasurements',
            'soilMeasurements' => fn ($q) => $q->latest('measurement_date'),
        ])->where('status', 'active')->get();

        $methodologies = MethodologyVersion::with('methodology')
            ->where('status', 'active')
            ->get();

        return Inertia::render('calculations/Create', [
            'blocks' => $blocks,
            'methodologies' => $methodologies,
        ]);
    }

    public function simulate(CalculationRunRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $block = LandBlock::findOrFail($validated['land_block_id']);
        $methodologyVer = MethodologyVersion::with(['calculationParameters.versions', 'methodology'])
            ->findOrFail($validated['methodology_version_id']);

        $previousRun = null;
        if (! empty($validated['previous_calculation_run_id'])) {
            $previousRun = CalculationRun::with('result')->find($validated['previous_calculation_run_id']);
        }

        $inputDTO = $this->buildInputDTO($block, $validated);
        $resultDTO = $this->calculationEngine->execute($inputDTO, $methodologyVer, $previousRun);

        return response()->json([
            'success' => true,
            'preview' => [
                'total_biomass_t' => $resultDTO->totalBiomassTon,
                'total_carbon_t' => $resultDTO->totalCarbonTon,
                'total_co2e_t' => $resultDTO->totalCo2eTon,
                'carbon_stock_t_ha' => $resultDTO->carbonStockTonPerHa,
                'co2e_stock_t_ha' => $resultDTO->co2eStockTonPerHa,
                'pools_breakdown' => $resultDTO->poolsBreakdown,
                'formula_snapshot' => $resultDTO->formulaSnapshot,
                'sequestration_t_co2e_year' => $resultDTO->sequestrationCo2eYear,
                'comparison_delta' => $resultDTO->comparisonDelta,
            ],
        ]);
    }

    public function store(CalculationRunRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $block = LandBlock::findOrFail($validated['land_block_id']);
        $methodologyVer = MethodologyVersion::with(['calculationParameters.versions', 'methodology'])
            ->findOrFail($validated['methodology_version_id']);

        $previousRun = null;
        if (! empty($validated['previous_calculation_run_id'])) {
            $previousRun = CalculationRun::with('result')->find($validated['previous_calculation_run_id']);
        }

        $inputDTO = $this->buildInputDTO($block, $validated);
        $resultDTO = $this->calculationEngine->execute($inputDTO, $methodologyVer, $previousRun);

        $run = DB::transaction(function () use ($validated, $block, $methodologyVer, $resultDTO, $previousRun) {
            $calcRun = CalculationRun::create([
                'organization_id' => $block->estate->organization_id,
                'estate_id' => $block->estate_id,
                'land_block_id' => $block->id,
                'methodology_version_id' => $methodologyVer->id,
                'executed_by' => Auth::id() ?? 1,
                'ecosystem_type' => $block->ecosystem_type,
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'area_ha' => $block->area_ha,
                'calculation_type' => $validated['calculation_type'] ?? 'stock_only',
                'status' => $validated['submit_for_approval'] ?? false ? 'pending_approval' : 'draft',
                'input_snapshot' => $resultDTO->inputSnapshot,
                'formula_snapshot' => $resultDTO->formulaSnapshot,
                'parameter_snapshot' => $resultDTO->parameterSnapshot,
                'calculated_at' => now(),
            ]);

            // Simpan Calculation Result
            $calcResult = CalculationResult::create([
                'calculation_run_id' => $calcRun->id,
                'total_biomass_t' => $resultDTO->totalBiomassTon,
                'total_carbon_t' => $resultDTO->totalCarbonTon,
                'total_co2e_t' => $resultDTO->totalCo2eTon,
                'carbon_stock_t_ha' => $resultDTO->carbonStockTonPerHa,
                'co2e_stock_t_ha' => $resultDTO->co2eStockTonPerHa,
                'sequestration_t_co2e_year' => $resultDTO->sequestrationCo2eYear,
            ]);

            // Simpan rincian 5 kolam karbon
            $carbonPoolsMap = CarbonPool::pluck('id', 'code');
            foreach ($resultDTO->poolsBreakdown as $code => $data) {
                if (isset($carbonPoolsMap[$code])) {
                    CarbonPoolResult::create([
                        'calculation_result_id' => $calcResult->id,
                        'carbon_pool_id' => $carbonPoolsMap[$code],
                        'biomass_t' => $data['biomass_t'],
                        'carbon_t' => $data['carbon_t'],
                        'co2e_t' => $data['co2e_t'],
                        'co2e_per_ha' => $data['co2e_per_ha'],
                    ]);
                }
            }

            // Simpan comparison jika sekuestrasi
            if ($previousRun && $resultDTO->comparisonDelta) {
                CalculationComparison::create([
                    'calculation_run_id' => $calcRun->id,
                    'previous_calculation_run_id' => $previousRun->id,
                    'delta_carbon_t' => $resultDTO->comparisonDelta['delta_carbon_t'],
                    'delta_co2e_t' => $resultDTO->comparisonDelta['delta_co2e_t'],
                    'annualized_co2e_t' => $resultDTO->comparisonDelta['annualized_co2e_t'],
                ]);
            }

            AuditLogger::log('calculate', $calcRun, null, [
                'status' => $calcRun->status,
                'total_co2e_t' => $resultDTO->totalCo2eTon,
            ]);

            return $calcRun;
        });

        return redirect()->route('calculations.show', $run->id)
            ->with('success', 'Kalkulasi karbon berhasil dieksekusi dan disimpan.');
    }

    public function show(string $id): Response
    {
        $run = CalculationRun::with([
            'landBlock.estate',
            'methodologyVersion.methodology',
            'executor',
            'result.poolResults.carbonPool',
            'comparisons.previousCalculationRun.result',
            'approvals.reviewer',
        ])->findOrFail($id);

        return Inertia::render('calculations/Show', [
            'run' => $run,
        ]);
    }

    public function destroy(string $id): RedirectResponse
    {
        $run = CalculationRun::findOrFail($id);

        if ($run->isApproved()) {
            return back()->with('error', 'Kalkulasi yang sudah disetujui terkunci permanen dan tidak dapat dihapus.');
        }

        $run->delete();

        return redirect()->route('calculations.index')
            ->with('success', 'Draf kalkulasi berhasil dihapus.');
    }

    protected function buildInputDTO(LandBlock $block, array $validated): CalculationInputDTO
    {
        $latestSoil = $block->soilMeasurements()->latest('measurement_date')->first();
        $latestField = $block->fieldMeasurements()->latest('measurement_date')->first();

        // Data Pohon Hutan
        $trees = [];
        if ($latestField && $block->ecosystem_type !== 'oil_palm') {
            $trees = $latestField->treeMeasurements->map(fn ($t) => [
                'species' => $t->botanical_species,
                'dbh_cm' => (float) $t->dbh_cm,
                'height_m' => (float) ($t->total_height_m ?? 0),
                'wood_density' => (float) $t->wood_density_g_cm3,
            ])->toArray();
        }

        // Data Kelapa Sawit
        $palmData = null;
        if ($block->ecosystem_type === 'oil_palm') {
            $cycle = $block->cropCycles()->latest()->first();
            $stand = $cycle?->palmStands()->latest()->first();
            $palmMeas = $latestField?->palmMeasurements()->first();

            $palmData = [
                'tree_count' => (int) ($stand->tree_count ?? 3500),
                'density_per_ha' => (float) ($validated['custom_density_ha'] ?? $stand->density_per_ha ?? 136.0),
                'growth_stage' => (string) ($stand->growth_stage ?? 'TM_PRIMA'),
                'avg_height_m' => (float) ($validated['custom_height_m'] ?? $palmMeas->avg_height_m ?? $stand->average_height_m ?? 4.5),
                'avg_stem_diameter_cm' => (float) ($palmMeas->avg_stem_diameter_cm ?? $stand->average_stem_diameter_cm ?? 50.0),
                'avg_frond_count' => (float) ($palmMeas->avg_frond_count ?? 38.0),
                'avg_frond_biomass_kg' => (float) ($palmMeas->avg_frond_biomass_kg ?? 3.5),
                'avg_fruit_bunch_biomass_kg' => (float) ($palmMeas->avg_fruit_bunch_biomass_kg ?? 18.0),
                'annual_pruned_fronds' => (float) ($palmMeas->annual_pruned_fronds ?? 38.0),
                'age_years' => (int) ($stand->age_years ?? ($cycle ? (now()->year - $cycle->planting_year) : 8)),
            ];
        }

        // Data Tanah
        $soilData = [
            'soil_type' => $block->soil_type,
            'depth_cm' => (float) ($latestSoil->depth_cm ?? 30.0),
            'bulk_density' => (float) ($validated['custom_bulk_density'] ?? $latestSoil->bulk_density ?? 1.15),
            'soil_carbon_percent' => (float) ($validated['custom_soil_c_pct'] ?? $latestSoil->soil_carbon_percent ?? 2.4),
            'coarse_fragments_percent' => (float) ($latestSoil->coarse_fragments_percent ?? 0.0),
        ];

        return new CalculationInputDTO(
            ecosystemType: $block->ecosystem_type,
            areaHa: (float) $block->area_ha,
            trees: $trees,
            palmData: $palmData,
            soilData: $soilData
        );
    }
}
