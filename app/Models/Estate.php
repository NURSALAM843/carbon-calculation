<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'code',
        'name',
        'total_area_ha',
        'province',
        'regency',
        'district',
        'village',
        'latitude',
        'longitude',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_area_ha' => 'decimal:4',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function landBlocks(): HasMany
    {
        return $this->hasMany(LandBlock::class);
    }

    public function calculationRuns(): HasMany
    {
        return $this->hasMany(CalculationRun::class);
    }

    /**
     * Scope query to apply dynamic filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['organization_id'] ?? null, fn ($q, $orgId) => $q->where('organization_id', $orgId))
            ->when($filters['province'] ?? null, fn ($q, $province) => $q->where('province', $province));
    }
}
