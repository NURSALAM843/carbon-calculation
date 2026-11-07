<?php

namespace App\DTOs\Calculation;

class BiomassResultDTO
{
    /**
     * @param  float  $agbTotalTon  Total biomassa AGB dalam ton untuk seluruh blok
     * @param  float  $agbTonPerHa  Biomassa AGB dalam ton per hektar
     * @param  float  $biomassPerTreeKg  Rata-rata biomassa per pohon (kg)
     * @param  string  $formulaUsed  Representasi formula alometrik yang diaplikasikan
     * @param  array<string, mixed>  $componentBreakdown  Rincian komponen (trunk, fronds, branches, etc)
     */
    public function __construct(
        public float $agbTotalTon,
        public float $agbTonPerHa,
        public float $biomassPerTreeKg,
        public string $formulaUsed,
        public array $componentBreakdown = [],
    ) {}
}
