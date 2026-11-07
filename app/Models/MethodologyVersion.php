<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MethodologyVersion extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'methodology_id',
        'version',
        'effective_from',
        'effective_to',
        'formula_definition',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'formula_definition' => 'array',
        ];
    }

    public function methodology(): BelongsTo
    {
        return $this->belongsTo(Methodology::class);
    }

    public function calculationParameters(): HasMany
    {
        return $this->hasMany(CalculationParameter::class);
    }

    public function calculationRuns(): HasMany
    {
        return $this->hasMany(CalculationRun::class);
    }
}
