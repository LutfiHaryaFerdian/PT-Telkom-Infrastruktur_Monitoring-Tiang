<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TiangRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tiangId = $this->route('id') ?? $this->route('tiang')?->id;

        return [
            'sto_id'                      => ['required', 'integer', 'exists:stos,id'],
            'jenis_tiang_id'              => ['required', 'integer', 'exists:jenis_tiang,id'],
            'kondisi_tiang_id'            => ['required', 'integer', 'exists:kondisi_tiang,id'],
            'latitude'                    => ['required', 'numeric', 'between:-7.0,-4.0'],
            'longitude'                   => ['required', 'numeric', 'between:104.0,107.0'],
            'nama_jalan'                  => ['required', 'string', 'max:1000', 'not_regex:/^\s+$/'],
            'jml_tiang_operator_sekitar'  => ['required', 'integer', 'min:0'],
            'jml_kabel_dc_telkom'         => ['required', 'integer', 'min:0'],
            'jml_ku_telkom'               => ['required', 'integer', 'min:0'],
            'nama_teknisi'                => ['nullable', 'string', 'max:200'],
            'tgl_input'                   => ['required', 'date'],
            'tanggal_temuan'              => ['nullable', 'date'],
            'id_tiang_instansi'           => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('tiang_telekomunikasi', 'id_tiang_instansi')
                    ->whereNotNull('id_tiang_instansi')
                    ->ignore($tiangId),
            ],

            // ISP data (optional array)
            'operators'                   => ['nullable', 'array'],
            'operators.*.operator_id'     => ['required_with:operators', 'integer', 'exists:operator_isp,id'],
            'operators.*.jml_kabel_dc'    => ['nullable', 'integer', 'min:0'],
            'operators.*.jml_ku'          => ['nullable', 'integer', 'min:0'],
            'operators.*.jml_odp'         => ['nullable', 'integer', 'min:0'],
            'operators.*.keterangan'      => ['nullable', 'string', 'max:1000'],
            'operators.*.status_legalitas'=> ['nullable', Rule::in(['legal', 'ilegal', 'perlu_verifikasi'])],
        ];
    }

    /**
     * Custom error messages dalam bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'sto_id.required'         => 'STO wajib dipilih.',
            'sto_id.exists'           => 'STO yang dipilih tidak valid.',
            'jenis_tiang_id.required' => 'Jenis tiang wajib dipilih.',
            'kondisi_tiang_id.required'=> 'Kondisi tiang wajib dipilih.',
            'latitude.required'       => 'Latitude wajib diisi.',
            'latitude.between'        => 'Latitude harus antara -7.0 dan -4.0 (area Lampung).',
            'longitude.required'      => 'Longitude wajib diisi.',
            'longitude.between'       => 'Longitude harus antara 104.0 dan 107.0 (area Lampung).',
            'nama_jalan.required'     => 'Nama jalan wajib diisi.',
            'nama_jalan.regex'        => 'Nama jalan tidak boleh hanya terdiri dari spasi.',
            'nama_jalan.not_regex'    => 'Nama jalan tidak boleh hanya terdiri dari spasi.',
            'tgl_input.required'      => 'Tanggal input wajib diisi.',
            'id_tiang_instansi.unique'=> 'ID tiang instansi sudah digunakan oleh tiang lain.',
        ];
    }

    /**
     * Prepare the data for validation: trim nama_jalan.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('nama_jalan')) {
            $this->merge(['nama_jalan' => trim($this->nama_jalan)]);
        }
        if ($this->has('nama_teknisi') && $this->nama_teknisi !== null) {
            $this->merge(['nama_teknisi' => trim($this->nama_teknisi)]);
        }
    }
}
