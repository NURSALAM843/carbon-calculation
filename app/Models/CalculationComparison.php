<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculationComparison extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'calculation_run_id',
        'previous_calculation_run_id',
        'delta_carbon_t',
        'delta_co2e_t',
        'annualized_co2e_t',
    ];

    protected function casts(): array
    {
        return [
            'delta_carbon_t' => 'decimal:4',
            'delta_co2e_t' => 'decimal:4',
            'annualized_co2e_t' => 'decimal:4',
        ];
    }

    public function calculationRun(): BelongsTo
    {
        return $this->belongsTo(CalculationRun::class);
    }

    public function previousCalculationRun(): BelongsTo
    {
        return $this->belongsTo(CalculationRun::class, 'previous_calculation_run_id');
    }
}
