<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSoilMeasurementRequest extends FormRequest
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
            'measurement_date' => ['required', 'date'],
            'soil_type' => ['required', 'string', 'max:150'],
            'depth_cm' => ['required', 'numeric', 'min:5', 'max:300'],
            'bulk_density' => ['required', 'numeric', 'min:0.05', 'max:2.5'],
            'soil_carbon_percent' => ['required', 'numeric', 'min:0.1', 'max:70.0'],
            'coarse_fragments_percent' => ['nullable', 'numeric', 'min:0', 'max:90'],
            'laboratory' => ['nullable', 'string', 'max:150'],
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
            'depth_cm.between' => 'Kedalaman profil tanah sampel berada dalam rentang 5 - 300 cm.',
            'soil_carbon_percent.max' => 'Persentase C-organik maksimal 70.0% (lapisan gambut murni).',
        ];
    }
}
