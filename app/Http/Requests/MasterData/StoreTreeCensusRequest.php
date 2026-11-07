<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTreeCensusRequest extends FormRequest
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
            'botanical_species' => ['required', 'string', 'max:150'],
            'wood_density_g_cm3' => ['required', 'numeric', 'min:0.1', 'max:1.5'],
            'dbh_cm' => ['required', 'numeric', 'min:5.0', 'max:300.0'],
            'total_height_m' => ['nullable', 'numeric', 'min:1.0', 'max:90.0'],
            'tree_tag_number' => ['nullable', 'string', 'max:50'],
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
            'dbh_cm.min' => 'Diameter batang (DBH) minimal 5.0 cm untuk standar inventarisasi pohon hutan.',
            'wood_density_g_cm3.between' => 'Berat jenis kayu (wood density) harus berada di rentang 0.1 - 1.5 g/cm³.',
        ];
    }
}
