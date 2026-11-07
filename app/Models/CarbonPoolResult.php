<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarbonPoolResult extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'calculation_result_id',
        'carbon_pool_id',
        'biomass_t',
        'carbon_t',
        'co2e_t',
        'co2e_per_ha',
    ];

    protected function casts(): array
    {
        return [
            'biomass_t' => 'decimal:4',
            'carbon_t' => 'decimal:4',
            'co2e_t' => 'decimal:4',
            'co2e_per_ha' => 'decimal:4',
        ];
    }

    public function calculationResult(): BelongsTo
    {
        return $this->belongsTo(CalculationResult::class);
    }

    public function carbonPool(): BelongsTo
    {
        return $this->belongsTo(CarbonPool::class);
    }
}
