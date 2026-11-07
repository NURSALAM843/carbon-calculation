<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FieldMeasurement extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'land_block_id',
        'measurement_date',
        'measurement_year',
        'measurement_method',
        'plot_area_m2',
        'status',
        'measured_by',
    ];

    protected function casts(): array
    {
        return [
            'measurement_date' => 'date',
            'measurement_year' => 'integer',
            'plot_area_m2' => 'decimal:2',
        ];
    }

    public function landBlock(): BelongsTo
    {
        return $this->belongsTo(LandBlock::class);
    }

    public function surveyor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'measured_by');
    }

    public function palmMeasurements(): HasMany
    {
        return $this->hasMany(PalmMeasurement::class);
    }

    public function treeMeasurements(): HasMany
    {
        return $this->hasMany(TreeMeasurement::class);
    }

    public function evidenceFiles(): HasMany
    {
        return $this->hasMany(EvidenceFile::class);
    }
}
