<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadEvidenceRequest extends FormRequest
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
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png,xlsx,csv'], // Max 20MB
            'land_block_id' => ['nullable', 'uuid', 'exists:land_blocks,id'],
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
            'file.required' => 'Dokumen bukti fisik/digital wajib diunggah.',
            'file.max' => 'Ukuran berkas tidak boleh melebihi 20 MB.',
            'file.mimes' => 'Format berkas harus berupa PDF, JPG, PNG, XLSX, atau CSV.',
        ];
    }
}
