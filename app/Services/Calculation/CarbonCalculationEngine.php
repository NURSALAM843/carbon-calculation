<?php

namespace App\Services\Calculation;

use App\Contracts\Calculation\EcosystemStrategyInterface;
use App\DTOs\Calculation\CalculationInputDTO;
use App\DTOs\Calculation\CalculationResultDTO;
use App\Models\CalculationRun;
use App\Models\MethodologyVersion;
use App\Services\Calculation\Strategies\HtiTimberStrategy;
use App\Services\Calculation\Strategies\MangroveStrategy;
use App\Services\Calculation\Strategies\NaturalForestStrategy;
use App\Services\Calculation\Strategies\OilPalmGrowthMutationStrategy;

class CarbonCalculationEngine
{
    /** @var array<EcosystemStrategyInterface> */
    protected array $strategies;

    public function __construct(
        protected SoilCarbonCalculator $soilCalculator,
        protected SequestrationEngine $sequestrationEngine,
        ?array $strategies = null
    ) {
        $this->strategies = $strategies ?? [
            new OilPalmGrowthMutationStrategy,
            new NaturalForestStrategy,
            new HtiTimberStrategy,
            new MangroveStrategy,
        ];
    }

    /**
     * Jalankan eksekusi perhitungan karbon lengkap 5 pools IPCC.
     */
    public function execute(
        CalculationInputDTO $input,
        MethodologyVersion $methodologyVersion,
        ?CalculationRun $previousRun = null
    ): CalculationResultDTO {
        // 1. Dapatkan strategi ekosistem yang sesuai
        $strategy = $this->resolveStrategy($input->ecosystemType);

        // 2. Load parameter aktif dari versi metodologi
        $parameters = $this->loadParameters($methodologyVersion);
        $cf = $parameters['CF'] ?? 0.47;
        $co2Ratio = $parameters['CO2_TO_C_RATIO'] ?? (44 / 12);

        // 3. Hitung AGB (Above Ground Biomass)
        $agbResult = $strategy->calculateAboveGroundBiomass($input, $parameters);
        $agbTonPerHa = $agbResult->agbTonPerHa;
        $agbTotalTon = $agbResult->agbTotalTon;
        $agbCarbonTon = round($agbTotalTon * $cf, 4);
        $agbCo2eTon = round($agbCarbonTon * $co2Ratio, 4);

        // 4. Hitung BGB (Below Ground Biomass)
        $bgbTonPerHa = $strategy->calculateBelowGroundBiomass($agbTonPerHa, $input, $parameters);
        $bgbTotalTon = round($bgbTonPerHa * $input->areaHa, 4);
        $bgbCarbonTon = round($bgbTotalTon * $cf, 4);
        $bgbCo2eTon = round($bgbCarbonTon * $co2Ratio, 4);

        // 5. Hitung Necromass (Deadwood & Litter)
        $necromass = $strategy->calculateNecromass($agbTonPerHa, $input, $parameters);
        $deadwoodTonPerHa = $necromass['deadwood_t_ha'] ?? 0.0;
        $deadwoodTotalTon = round($deadwoodTonPerHa * $input->areaHa, 4);
        $deadwoodCarbonTon = round($deadwoodTotalTon * $cf, 4);
        $deadwoodCo2eTon = round($deadwoodCarbonTon * $co2Ratio, 4);

        $litterTonPerHa = $necromass['litter_t_ha'] ?? 0.0;
        $litterTotalTon = round($litterTonPerHa * $input->areaHa, 4);
        $litterCarbonTon = round($litterTotalTon * $cf, 4);
        $litterCo2eTon = round($litterCarbonTon * $co2Ratio, 4);

        // 6. Hitung SOC (Soil Organic Carbon)
        $soilResult = $this->soilCalculator->calculate($input->soilData, $input->areaHa);
        $soilCarbonTon = $soilResult['soc_total_t_c'];
        $soilCo2eTon = $soilResult['soc_total_co2e'];
        $soilTonPerHa = $soilResult['soc_t_c_ha'];

        // 7. Agregasi 5 Kolam Karbon IPCC
        $totalBiomassTon = round($agbTotalTon + $bgbTotalTon + $deadwoodTotalTon + $litterTotalTon, 4);
        $totalCarbonTon = round($agbCarbonTon + $bgbCarbonTon + $deadwoodCarbonTon + $litterCarbonTon + $soilCarbonTon, 4);
        $totalCo2eTon = round($totalCarbonTon * $co2Ratio, 4);

        $carbonStockPerHa = $input->areaHa > 0 ? round($totalCarbonTon / $input->areaHa, 4) : 0.0;
        $co2eStockPerHa = $input->areaHa > 0 ? round($totalCo2eTon / $input->areaHa, 4) : 0.0;

        // 8. Rincian Kolam Karbon
        $poolsBreakdown = [
            'AGB' => [
                'biomass_t' => $agbTotalTon,
                'carbon_t' => $agbCarbonTon,
                'co2e_t' => $agbCo2eTon,
                'co2e_per_ha' => $input->areaHa > 0 ? round($agbCo2eTon / $input->areaHa, 4) : 0.0,
            ],
            'BGB' => [
                'biomass_t' => $bgbTotalTon,
                'carbon_t' => $bgbCarbonTon,
                'co2e_t' => $bgbCo2eTon,
                'co2e_per_ha' => $input->areaHa > 0 ? round($bgbCo2eTon / $input->areaHa, 4) : 0.0,
            ],
            'DEADWOOD' => [
                'biomass_t' => $deadwoodTotalTon,
                'carbon_t' => $deadwoodCarbonTon,
                'co2e_t' => $deadwoodCo2eTon,
                'co2e_per_ha' => $input->areaHa > 0 ? round($deadwoodCo2eTon / $input->areaHa, 4) : 0.0,
            ],
            'LITTER' => [
                'biomass_t' => $litterTotalTon,
                'carbon_t' => $litterCarbonTon,
                'co2e_t' => $litterCo2eTon,
                'co2e_per_ha' => $input->areaHa > 0 ? round($litterCo2eTon / $input->areaHa, 4) : 0.0,
            ],
            'SOIL' => [
                'biomass_t' => 0.0, // Karbon tanah tidak memiliki metrik biomassa terpisah
                'carbon_t' => $soilCarbonTon,
                'co2e_t' => $soilCo2eTon,
                'co2e_per_ha' => $input->areaHa > 0 ? round($soilCo2eTon / $input->areaHa, 4) : 0.0,
            ],
        ];

        // 9. Multi-Period Sequestration jika ada previous run
        $sequestrationCo2eYear = null;
        $comparisonDelta = null;

        if ($previousRun && $previousRun->result) {
            $comparisonDelta = $this->sequestrationEngine->calculateDelta(
                carbonT1: (float) $previousRun->result->total_carbon_t,
                dateT1: $previousRun->period_end,
                carbonT2: $totalCarbonTon,
                dateT2: now()->toDateString(),
                areaHa: $input->areaHa
            );
            $sequestrationCo2eYear = $comparisonDelta['annualized_co2e_t'];
        }

        // 10. Snapshots Audit
        $inputSnapshot = [
            'ecosystem_type' => $input->ecosystemType,
            'area_ha' => $input->areaHa,
            'palm_data' => $input->palmData,
            'tree_count' => count($input->trees),
            'soil_data' => $input->soilData,
        ];

        $formulaSnapshot = [
            'agb_model' => $agbResult->formulaUsed,
            'bgb_model' => 'BGB = AGB * RootShootRatio',
            'soc_model' => $soilResult['method_applied'],
            'carbon_fraction' => "C = Biomass * {$cf}",
            'co2e_conversion' => 'CO2e = C * (44 / 12)',
            'methodology_code' => $methodologyVersion->methodology->code ?? 'CUSTOM',
            'methodology_version' => $methodologyVersion->version,
        ];

        $parameterSnapshot = $parameters;

        return new CalculationResultDTO(
            totalBiomassTon: $totalBiomassTon,
            totalCarbonTon: $totalCarbonTon,
            totalCo2eTon: $totalCo2eTon,
            carbonStockTonPerHa: $carbonStockPerHa,
            co2eStockTonPerHa: $co2eStockPerHa,
            poolsBreakdown: $poolsBreakdown,
            inputSnapshot: $inputSnapshot,
            formulaSnapshot: $formulaSnapshot,
            parameterSnapshot: $parameterSnapshot,
            sequestrationCo2eYear: $sequestrationCo2eYear,
            comparisonDelta: $comparisonDelta
        );
    }

    protected function resolveStrategy(string $ecosystemType): EcosystemStrategyInterface
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($ecosystemType)) {
                return $strategy;
            }
        }

        // Default fallback to NaturalForestStrategy
        return $this->strategies[1];
    }

    /**
     * @return array<string, float>
     */
    protected function loadParameters(MethodologyVersion $version): array
    {
        $parameters = [];

        foreach ($version->calculationParameters as $param) {
            $currentVer = $param->currentVersion();
            if ($currentVer && $currentVer->numeric_value !== null) {
                $parameters[$param->code] = (float) $currentVer->numeric_value;
            }
        }

        // Default fallback constants jika belum di-seed
        $parameters['CF'] ??= 0.47;
        $parameters['CO2_TO_C_RATIO'] ??= 3.666667;

        return $parameters;
    }
}
