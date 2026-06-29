<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\District;
use App\Models\JenisTiang;
use App\Models\KondisiTiang;
use App\Models\Sto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    // ============================================================
    // HELPERS
    // ============================================================

    protected function notRegex(): string
    {
        return '/^\s+$/';
    }

    protected function trimName(Request $request, string $field): void
    {
        if ($request->has($field)) {
            $request->merge([$field => trim($request->input($field))]);
        }
    }

    // ============================================================
    // DISTRICT
    // ============================================================

    public function index(): View
    {
        $districts = District::withCount('areas')->orderBy('name')->get();
        return view('master.districts', compact('districts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->trimName($request, 'name');
        $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::notRegex($this->notRegex()), 'unique:districts,name'],
        ], ['name.regex' => 'Nama tidak boleh hanya terdiri dari spasi.']);

        District::create(['name' => $request->name]);
        return back()->with('success', 'District berhasil ditambahkan.');
    }

    public function update(Request $request, District $district): RedirectResponse
    {
        $this->trimName($request, 'name');
        $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::notRegex($this->notRegex()), Rule::unique('districts', 'name')->ignore($district->id)],
        ], ['name.regex' => 'Nama tidak boleh hanya terdiri dari spasi.']);

        $district->update(['name' => $request->name]);
        return back()->with('success', 'District berhasil diperbarui.');
    }

    public function destroy(District $district): RedirectResponse
    {
        if ($district->areas()->count() > 0) {
            return back()->with('error', 'District tidak bisa dihapus karena masih memiliki ' . $district->areas()->count() . ' area.');
        }
        $district->delete();
        return back()->with('success', 'District berhasil dihapus.');
    }

    // ============================================================
    // AREA
    // ============================================================

    public function areasIndex(): View
    {
        $areas = Area::with('district')->orderBy('name')->get();
        $districts = District::orderBy('name')->get();
        return view('master.areas', compact('areas', 'districts'));
    }

    public function areasStore(Request $request): RedirectResponse
    {
        $this->trimName($request, 'name');
        $request->validate([
            'district_id' => ['required', 'exists:districts,id'],
            'name'        => ['required', 'string', 'max:100', Rule::notRegex($this->notRegex()), Rule::unique('areas')->where('district_id', $request->district_id)],
        ], ['name.unique' => 'Nama area sudah ada di district ini.']);

        Area::create($request->only('district_id', 'name'));
        return back()->with('success', 'Area berhasil ditambahkan.');
    }

    public function areasUpdate(Request $request, Area $area): RedirectResponse
    {
        $this->trimName($request, 'name');
        $request->validate([
            'district_id' => ['required', 'exists:districts,id'],
            'name'        => ['required', 'string', 'max:100', Rule::notRegex($this->notRegex()),
                Rule::unique('areas')->where('district_id', $request->district_id)->ignore($area->id)],
        ]);

        $area->update($request->only('district_id', 'name'));
        return back()->with('success', 'Area berhasil diperbarui.');
    }

    public function areasDestroy(Area $area): RedirectResponse
    {
        if ($area->stos()->count() > 0) {
            return back()->with('error', 'Area tidak bisa dihapus karena masih memiliki ' . $area->stos()->count() . ' STO.');
        }
        $area->delete();
        return back()->with('success', 'Area berhasil dihapus.');
    }

    // ============================================================
    // STO
    // ============================================================

    public function stosIndex(): View
    {
        $stos = Sto::with('area.district')->withCount('tiangTelekomunikasi')->orderBy('kode')->get();
        $areas = Area::with('district')->orderBy('name')->get();
        return view('master.stos', compact('stos', 'areas'));
    }

    public function stosTrashed(): View
    {
        $stos = Sto::onlyTrashed()->with('area.district')->orderBy('kode')->get();
        return view('master.stos-trashed', compact('stos'));
    }

    public function stosStore(Request $request): RedirectResponse
    {
        $request->validate([
            'area_id' => ['required', 'exists:areas,id'],
            'kode'    => ['required', 'string', 'max:20', 'unique:stos,kode'],
            'nama'    => ['nullable', 'string', 'max:100'],
        ]);

        Sto::create([
            'area_id' => $request->area_id,
            'kode'    => strtoupper(trim($request->kode)),
            'nama'    => $request->nama ? trim($request->nama) : null,
        ]);

        return back()->with('success', 'STO berhasil ditambahkan.');
    }

    public function stosUpdate(Request $request, Sto $sto): RedirectResponse
    {
        $request->validate([
            'area_id' => ['required', 'exists:areas,id'],
            'kode'    => ['required', 'string', 'max:20', Rule::unique('stos', 'kode')->ignore($sto->id)],
            'nama'    => ['nullable', 'string', 'max:100'],
        ]);

        $sto->update([
            'area_id' => $request->area_id,
            'kode'    => strtoupper(trim($request->kode)),
            'nama'    => $request->nama ? trim($request->nama) : null,
        ]);

        return back()->with('success', 'STO berhasil diperbarui.');
    }

    public function stosDestroy(Sto $sto): RedirectResponse
    {
        // Cek tiang aktif sebelum soft delete
        $activeTiang = $sto->countActiveTiang();
        if ($activeTiang > 0) {
            return back()->with('error', "STO {$sto->kode} tidak bisa dihapus karena masih digunakan oleh {$activeTiang} tiang aktif.");
        }

        $sto->delete();
        return back()->with('success', "STO {$sto->kode} berhasil dihapus.");
    }

    public function stosRestore(int $id): RedirectResponse
    {
        $sto = Sto::onlyTrashed()->findOrFail($id);
        $sto->restore();
        return back()->with('success', "STO {$sto->kode} berhasil dipulihkan.");
    }

    // ============================================================
    // JENIS TIANG
    // ============================================================

    public function jenisTiangIndex(): View
    {
        $jenisTiang = JenisTiang::withCount('tiangTelekomunikasi')->orderBy('nama')->get();
        return view('master.jenis-tiang', compact('jenisTiang'));
    }

    public function jenisTiangStore(Request $request): RedirectResponse
    {
        $request->merge(['nama' => trim($request->nama ?? '')]);
        $request->validate([
            'nama'       => ['required', 'string', 'max:100', Rule::notRegex($this->notRegex()), 'unique:jenis_tiang,nama'],
            'keterangan' => ['nullable', 'string'],
        ]);

        JenisTiang::create($request->only('nama', 'keterangan'));
        return back()->with('success', 'Jenis tiang berhasil ditambahkan.');
    }

    public function jenisTiangUpdate(Request $request, JenisTiang $jenisTiang): RedirectResponse
    {
        $request->merge(['nama' => trim($request->nama ?? '')]);
        $request->validate([
            'nama'       => ['required', 'string', 'max:100', Rule::notRegex($this->notRegex()), Rule::unique('jenis_tiang', 'nama')->ignore($jenisTiang->id)],
            'keterangan' => ['nullable', 'string'],
        ]);

        $jenisTiang->update($request->only('nama', 'keterangan'));
        return back()->with('success', 'Jenis tiang berhasil diperbarui.');
    }

    public function jenisTiangDestroy(JenisTiang $jenisTiang): RedirectResponse
    {
        if ($jenisTiang->tiangTelekomunikasi()->count() > 0) {
            return back()->with('error', 'Jenis tiang tidak bisa dihapus karena masih digunakan oleh ' . $jenisTiang->tiangTelekomunikasi()->count() . ' tiang.');
        }
        $jenisTiang->delete();
        return back()->with('success', 'Jenis tiang berhasil dihapus.');
    }

    // ============================================================
    // KONDISI TIANG
    // ============================================================

    public function kondisiTiangIndex(): View
    {
        $kondisiTiang = KondisiTiang::withCount('tiangTelekomunikasi')->orderBy('nama')->get();
        return view('master.kondisi-tiang', compact('kondisiTiang'));
    }

    public function kondisiTiangStore(Request $request): RedirectResponse
    {
        $request->merge(['nama' => trim($request->nama ?? '')]);
        $request->validate([
            'nama'  => ['required', 'string', 'max:100', Rule::notRegex($this->notRegex()), 'unique:kondisi_tiang,nama'],
            'level' => ['required', Rule::in(['baik', 'perlu_perhatian', 'rusak'])],
        ]);

        KondisiTiang::create($request->only('nama', 'level'));
        return back()->with('success', 'Kondisi tiang berhasil ditambahkan.');
    }

    public function kondisiTiangUpdate(Request $request, KondisiTiang $kondisiTiang): RedirectResponse
    {
        $request->merge(['nama' => trim($request->nama ?? '')]);
        $request->validate([
            'nama'  => ['required', 'string', 'max:100', Rule::notRegex($this->notRegex()), Rule::unique('kondisi_tiang', 'nama')->ignore($kondisiTiang->id)],
            'level' => ['required', Rule::in(['baik', 'perlu_perhatian', 'rusak'])],
        ]);

        $kondisiTiang->update($request->only('nama', 'level'));
        return back()->with('success', 'Kondisi tiang berhasil diperbarui.');
    }

    public function kondisiTiangDestroy(KondisiTiang $kondisiTiang): RedirectResponse
    {
        if ($kondisiTiang->tiangTelekomunikasi()->count() > 0) {
            return back()->with('error', 'Kondisi tiang tidak bisa dihapus karena masih digunakan oleh ' . $kondisiTiang->tiangTelekomunikasi()->count() . ' tiang.');
        }
        $kondisiTiang->delete();
        return back()->with('success', 'Kondisi tiang berhasil dihapus.');
    }
}
