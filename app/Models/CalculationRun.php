<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CalculationRun extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'estate_id',
        'land_block_id',
        'methodology_version_id',
        'executed_by',
        'ecosystem_type',
        'period_start',
        'period_end',
        'area_ha',
        'calculation_type',
        'status',
        'input_snapshot',
        'formula_snapshot',
        'parameter_snapshot',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'area_ha' => 'decimal:4',
            'input_snapshot' => 'array',
            'formula_snapshot' => 'array',
            'parameter_snapshot' => 'array',
            'calculated_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function estate(): BelongsTo
    {
        return $this->belongsTo(Estate::class);
    }

    public function landBlock(): BelongsTo
    {
        return $this->belongsTo(LandBlock::class);
    }

    public function methodologyVersion(): BelongsTo
    {
        return $this->belongsTo(MethodologyVersion::class);
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function inputs(): HasMany
    {
        return $this->hasMany(CalculationInput::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(CalculationResult::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }

    public function comparisons(): HasMany
    {
        return $this->hasMany(CalculationComparison::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending_approval';
    }

    /**
     * Scope query to apply dynamic filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['ecosystem_type'] ?? null, fn ($q, $type) => $q->where('ecosystem_type', $type))
            ->when($filters['estate_id'] ?? null, fn ($q, $estateId) => $q->where('estate_id', $estateId));
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }
}
