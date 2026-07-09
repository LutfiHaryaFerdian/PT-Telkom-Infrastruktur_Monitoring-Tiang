<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IspSuratRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tiang_operator_id' => ['required', 'exists:tiang_operator,id'],
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'jenis_surat' => ['required', 'in:pemberitahuan,peringatan,konfirmasi,tagihan,lainnya'],
            'tanggal_surat' => ['required', 'date', 'before_or_equal:today'],
            'perihal' => ['required', 'string', 'max:255'],
            'isi_ringkasan' => ['nullable', 'string'],
            'file_surat' => ['nullable', 'file', 'max:10240', 'mimes:pdf'],
        ];
    }
}
