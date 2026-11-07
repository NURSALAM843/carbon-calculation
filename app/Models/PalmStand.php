<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PalmStand extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'crop_cycle_id',
        'tree_count',
        'density_per_ha',
        'growth_stage',
        'average_height_m',
        'average_stem_diameter_cm',
        'age_years',
    ];

    protected function casts(): array
    {
        return [
            'tree_count' => 'integer',
            'density_per_ha' => 'decimal:2',
            'average_height_m' => 'decimal:2',
            'average_stem_diameter_cm' => 'decimal:2',
            'age_years' => 'integer',
        ];
    }

    public function cropCycle(): BelongsTo
    {
        return $this->belongsTo(CropCycle::class);
    }
}
