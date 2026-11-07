<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalculationResult extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'calculation_run_id',
        'total_biomass_t',
        'total_carbon_t',
        'total_co2e_t',
        'carbon_stock_t_ha',
        'co2e_stock_t_ha',
        'sequestration_t_co2e_year',
    ];

    protected function casts(): array
    {
        return [
            'total_biomass_t' => 'float',
            'total_carbon_t' => 'float',
            'total_co2e_t' => 'float',
            'carbon_stock_t_ha' => 'float',
            'co2e_stock_t_ha' => 'float',
            'sequestration_t_co2e_year' => 'float',
        ];
    }

    public function calculationRun(): BelongsTo
    {
        return $this->belongsTo(CalculationRun::class);
    }

    public function poolResults(): HasMany
    {
        return $this->hasMany(CarbonPoolResult::class);
    }
}
