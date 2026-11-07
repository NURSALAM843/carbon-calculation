<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalculationParameter extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'methodology_version_id',
        'code',
        'name',
        'unit',
        'description',
    ];

    public function methodologyVersion(): BelongsTo
    {
        return $this->belongsTo(MethodologyVersion::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ParameterVersion::class);
    }

    public function currentVersion(): ?ParameterVersion
    {
        return $this->versions()
            ->where(function ($q) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', now());
            })
            ->latest('effective_from')
            ->first();
    }
}
