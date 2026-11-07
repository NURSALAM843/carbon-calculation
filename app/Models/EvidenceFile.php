<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceFile extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'land_block_id',
        'field_measurement_id',
        'file_name',
        'file_path',
        'file_type',
        'hash',
    ];

    public function landBlock(): BelongsTo
    {
        return $this->belongsTo(LandBlock::class);
    }

    public function fieldMeasurement(): BelongsTo
    {
        return $this->belongsTo(FieldMeasurement::class);
    }
}
