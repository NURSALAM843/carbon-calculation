<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreeMeasurement extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'field_measurement_id',
        'tree_tag_number',
        'botanical_species',
        'wood_density_g_cm3',
        'dbh_cm',
        'total_height_m',
        'commercial_height_m',
        'crown_diameter_m',
    ];

    protected function casts(): array
    {
        return [
            'wood_density_g_cm3' => 'decimal:3',
            'dbh_cm' => 'decimal:2',
            'total_height_m' => 'decimal:2',
            'commercial_height_m' => 'decimal:2',
            'crown_diameter_m' => 'decimal:2',
        ];
    }

    public function fieldMeasurement(): BelongsTo
    {
        return $this->belongsTo(FieldMeasurement::class);
    }
}
