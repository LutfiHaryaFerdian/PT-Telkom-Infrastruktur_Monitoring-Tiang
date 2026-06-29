<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FotoInspeksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'fotos'        => ['required', 'array', 'max:10'],
            'fotos.*'      => [
                'required',
                'file',
                'mimes:jpg,jpeg,png',
                'max:5120', // maks 5MB per file
            ],
            'jenis_foto.*' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Validasi tambahan: total foto yang diupload + yang sudah ada tidak boleh > 10.
     * Ini validasi di Request layer (layer pertama).
     * Controller wajib cek ulang (layer kedua) sebelum simpan.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->hasFile('fotos') && count($this->file('fotos')) > 10) {
                $validator->errors()->add('fotos', 'Maksimal 10 foto per inspeksi.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'fotos.required' => 'Minimal satu foto wajib diupload.',
            'fotos.max'      => 'Maksimal 10 foto per inspeksi.',
            'fotos.*.mimes'  => 'Format foto harus jpg, jpeg, atau png.',
            'fotos.*.max'    => 'Ukuran setiap foto maksimal 5MB.',
        ];
    }
}
