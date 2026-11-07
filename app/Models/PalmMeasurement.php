<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PalmMeasurement extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'field_measurement_id',
        'sample_count',
        'avg_height_m',
        'avg_stem_diameter_cm',
        'avg_frond_count',
        'avg_frond_biomass_kg',
        'avg_fruit_bunch_biomass_kg',
        'annual_pruned_fronds',
    ];

    protected function casts(): array
    {
        return [
            'sample_count' => 'integer',
            'avg_height_m' => 'decimal:2',
            'avg_stem_diameter_cm' => 'decimal:2',
            'avg_frond_count' => 'decimal:2',
            'avg_frond_biomass_kg' => 'decimal:2',
            'avg_fruit_bunch_biomass_kg' => 'decimal:2',
            'annual_pruned_fronds' => 'decimal:2',
        ];
    }

    public function fieldMeasurement(): BelongsTo
    {
        return $this->belongsTo(FieldMeasurement::class);
    }
}
