<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CropCycle extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'land_block_id',
        'crop_type',
        'planting_date',
        'planting_year',
        'initial_population',
        'planting_density',
        'variety',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'planting_date' => 'date',
            'planting_year' => 'integer',
            'initial_population' => 'integer',
            'planting_density' => 'decimal:2',
        ];
    }

    public function landBlock(): BelongsTo
    {
        return $this->belongsTo(LandBlock::class);
    }

    public function palmStands(): HasMany
    {
        return $this->hasMany(PalmStand::class);
    }

    public function currentPalmStand(): ?PalmStand
    {
        return $this->palmStands()->latest()->first();
    }
}
