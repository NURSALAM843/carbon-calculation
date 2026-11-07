<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandBlock extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'estate_id',
        'code',
        'name',
        'ecosystem_type',
        'area_ha',
        'geometry',
        'soil_type',
        'land_type',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'area_ha' => 'decimal:4',
            'geometry' => 'array',
        ];
    }

    public function estate(): BelongsTo
    {
        return $this->belongsTo(Estate::class);
    }

    public function cropCycles(): HasMany
    {
        return $this->hasMany(CropCycle::class);
    }

    public function activeCropCycle(): ?CropCycle
    {
        return $this->cropCycles()->where('status', 'active')->latest()->first();
    }

    public function fieldMeasurements(): HasMany
    {
        return $this->hasMany(FieldMeasurement::class);
    }

    public function soilMeasurements(): HasMany
    {
        return $this->hasMany(SoilMeasurement::class);
    }

    public function calculationRuns(): HasMany
    {
        return $this->hasMany(CalculationRun::class);
    }

    public function evidenceFiles(): HasMany
    {
        return $this->hasMany(EvidenceFile::class);
    }

    /**
     * Scope query to apply dynamic filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['ecosystem_type'] ?? null, fn ($q, $type) => $q->where('ecosystem_type', $type))
            ->when($filters['estate_id'] ?? null, fn ($q, $estateId) => $q->where('estate_id', $estateId));
    }

    public function scopeOilPalm(Builder $query): Builder
    {
        return $query->where('ecosystem_type', 'oil_palm');
    }

    public function scopeForestEcosystems(Builder $query): Builder
    {
        return $query->whereIn('ecosystem_type', ['natural_forest', 'hti_plantation', 'mangrove']);
    }
}
