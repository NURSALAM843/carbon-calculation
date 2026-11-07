<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Methodology extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'description',
        'source',
        'status',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(MethodologyVersion::class);
    }

    public function activeVersion(): ?MethodologyVersion
    {
        return $this->versions()->where('status', 'active')->latest('effective_from')->first();
    }
}
