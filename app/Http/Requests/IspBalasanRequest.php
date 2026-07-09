<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IspBalasanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'isp_surat_id' => ['required', 'exists:isp_surat,id'],
            'tanggal_balasan' => ['required', 'date', 'before_or_equal:today'],
            'isi_ringkasan' => ['nullable', 'string'],
            'file_balasan' => ['nullable', 'file', 'max:10240', 'mimes:pdf'],
            'status_balasan' => ['required', 'in:positif,negatif,netral,perlu_tindaklanjut'],
        ];
    }
}
