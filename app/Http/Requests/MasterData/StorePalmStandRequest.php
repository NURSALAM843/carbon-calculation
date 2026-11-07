<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePalmStandRequest extends FormRequest
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
            'planting_year' => ['required', 'integer', 'min:1980', 'max:'.now()->year],
            'tree_count' => ['required', 'integer', 'min:1'],
            'growth_stage' => ['required', 'string', 'in:TBM_1,TBM_2,TBM_3,TM_MUDA,TM_PRIMA,TM_TUA'],
            'average_height_m' => ['required', 'numeric', 'min:0'],
            'avg_frond_count' => ['nullable', 'numeric', 'min:10', 'max:60'],
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
            'growth_stage.in' => 'Fase mutasi pertumbuhan harus salah satu dari: TBM_1, TBM_2, TBM_3, TM_MUDA, TM_PRIMA, TM_TUA.',
            'planting_year.max' => 'Tahun tanam tidak boleh melebihi tahun berjalan.',
        ];
    }
}
