<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLandBlockRequest extends FormRequest
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
            'estate_id' => ['required', 'uuid', 'exists:estates,id'],
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'ecosystem_type' => ['required', 'string', 'in:oil_palm,natural_forest,hti_plantation,mangrove,peat_swamp_forest,agroforestry'],
            'area_ha' => ['required', 'numeric', 'min:0.1'],
            'soil_type' => ['required', 'string', 'max:150'],
            'land_type' => ['required', 'string', 'max:150'],
            'geometry' => ['nullable', 'array'],
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
            'estate_id.required' => 'Estate induk wajib dipilih.',
            'ecosystem_type.in' => 'Tipe ekosistem tidak valid.',
            'area_ha.min' => 'Luas blok lahan minimal 0.1 hektar.',
        ];
    }
}
