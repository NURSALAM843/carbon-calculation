<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoilMeasurement extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'land_block_id',
        'measurement_date',
        'soil_type',
        'depth_cm',
        'bulk_density',
        'soil_carbon_percent',
        'coarse_fragments_percent',
        'soil_organic_carbon_t_ha',
        'laboratory',
    ];

    protected function casts(): array
    {
        return [
            'measurement_date' => 'date',
            'depth_cm' => 'decimal:2',
            'bulk_density' => 'decimal:3',
            'soil_carbon_percent' => 'decimal:3',
            'coarse_fragments_percent' => 'decimal:3',
            'soil_organic_carbon_t_ha' => 'decimal:4',
        ];
    }

    public function landBlock(): BelongsTo
    {
        return $this->belongsTo(LandBlock::class);
    }
}
