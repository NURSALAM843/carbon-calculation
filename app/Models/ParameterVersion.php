<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParameterVersion extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'calculation_parameter_id',
        'numeric_value',
        'value_json',
        'source',
        'source_reference',
        'effective_from',
        'effective_to',
    ];

    protected function casts(): array
    {
        return [
            'numeric_value' => 'decimal:6',
            'value_json' => 'array',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function calculationParameter(): BelongsTo
    {
        return $this->belongsTo(CalculationParameter::class);
    }
}
