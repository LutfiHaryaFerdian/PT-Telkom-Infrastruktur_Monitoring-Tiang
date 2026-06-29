<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FotoTiangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'jenis_foto' => ['required', 'in:depan,kanan,kiri'],
            'foto'       => [
                'required',
                'file',
                'mimes:jpg,jpeg,png',
                'max:5120', // maks 5MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_foto.required' => 'Jenis foto wajib dipilih (depan/kanan/kiri).',
            'jenis_foto.in'       => 'Jenis foto hanya boleh: depan, kanan, atau kiri.',
            'foto.required'       => 'File foto wajib diupload.',
            'foto.mimes'          => 'Format foto harus jpg, jpeg, atau png.',
            'foto.max'            => 'Ukuran foto maksimal 5MB.',
        ];
    }
}
