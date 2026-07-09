<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IspFollowupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tiang_operator_id' => ['required', 'exists:tiang_operator,id'],
            'tanggal_followup' => ['required', 'date', 'before_or_equal:today'],
            'metode' => ['required', 'in:telepon,email,kunjungan_langsung,rapat,whatsapp,lainnya'],
            'catatan' => ['required', 'string'],
            'hasil' => ['required', 'in:berhasil_dihubungi,tidak_ada_respons,dijadwalkan_ulang,selesai'],
            'file_bukti' => ['nullable', 'file', 'max:10240', 'mimes:jpeg,png,jpg,pdf'],
        ];
    }
}
