<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculationInput extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'calculation_run_id',
        'input_code',
        'numeric_value',
        'unit',
        'source_reference',
    ];

    protected function casts(): array
    {
        return [
            'numeric_value' => 'decimal:4',
            'source_reference' => 'array',
        ];
    }

    public function calculationRun(): BelongsTo
    {
        return $this->belongsTo(CalculationRun::class);
    }
}
