<?php

namespace App\Http\Requests\Calculation;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CalculationRunRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'land_block_id' => ['required', 'uuid', 'exists:land_blocks,id'],
            'methodology_version_id' => ['required', 'uuid', 'exists:methodology_versions,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'calculation_type' => ['nullable', 'string', 'in:stock_only,sequestration,full_accounting'],
            'previous_calculation_run_id' => ['nullable', 'uuid', 'exists:calculation_runs,id'],
            'submit_for_approval' => ['nullable', 'boolean'],
            // Parameter manual overrides opsional
            'custom_height_m' => ['nullable', 'numeric', 'min:0'],
            'custom_density_ha' => ['nullable', 'numeric', 'min:0'],
            'custom_soil_c_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'custom_bulk_density' => ['nullable', 'numeric', 'min:0.01', 'max:3.0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'land_block_id.required' => 'Blok lahan target wajib dipilih.',
            'land_block_id.exists' => 'Blok lahan yang dipilih tidak ditemukan dalam sistem.',
            'methodology_version_id.required' => 'Versi metodologi alometrik wajib dipilih.',
            'period_start.required' => 'Tanggal awal periode perhitungan wajib diisi.',
            'period_end.required' => 'Tanggal akhir periode perhitungan wajib diisi.',
            'period_end.after_or_equal' => 'Tanggal akhir periode harus sama dengan atau setelah tanggal awal.',
        ];
    }
}
