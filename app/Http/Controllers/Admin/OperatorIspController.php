<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperatorIsp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OperatorIspController extends Controller
{
    public function index(): View
    {
        $operators = OperatorIsp::withCount([
            'tiangOperator as tiang_count' => fn ($q) => $q->whereHas('tiang', fn ($tq) => $tq->whereNull('deleted_at')),
        ])->orderBy('nama_operator')->get();

        return view('master.operator-isp', compact('operators'));
    }

    public function trashed(): View
    {
        $operators = OperatorIsp::onlyTrashed()->orderBy('nama_operator')->get();
        return view('master.operator-isp-trashed', compact('operators'));
    }

    public function store(Request $request): RedirectResponse
    {
        $nama = trim($request->input('nama_operator', ''));
        $request->merge(['nama_operator' => $nama]);

        $request->validate([
            'nama_operator' => ['required', 'string', 'max:100', Rule::notRegex('/^\s+$/'), 'unique:operator_isp,nama_operator'],
        ]);

        OperatorIsp::create([
            'nama_operator' => $nama,
            'is_predefined' => $request->boolean('is_predefined'),
        ]);

        return back()->with('success', 'Operator ISP berhasil ditambahkan.');
    }

    public function update(Request $request, OperatorIsp $operatorIsp): RedirectResponse
    {
        $nama = trim($request->input('nama_operator', ''));
        $request->merge(['nama_operator' => $nama]);

        $request->validate([
            'nama_operator' => ['required', 'string', 'max:100', Rule::notRegex('/^\s+$/'),
                Rule::unique('operator_isp', 'nama_operator')->ignore($operatorIsp->id)],
        ]);

        $operatorIsp->update(['nama_operator' => $nama]);
        return back()->with('success', 'Operator ISP berhasil diperbarui.');
    }

    public function destroy(OperatorIsp $operatorIsp): RedirectResponse
    {
        // Cek apakah operator masih dipakai tiang aktif
        if ($operatorIsp->hasActiveTiang()) {
            return back()->with('error', "Operator {$operatorIsp->nama_operator} tidak bisa dihapus karena masih terpasang di tiang aktif.");
        }

        $operatorIsp->delete();
        return back()->with('success', "Operator {$operatorIsp->nama_operator} berhasil dihapus.");
    }

    public function restore(int $id): RedirectResponse
    {
        $operator = OperatorIsp::onlyTrashed()->findOrFail($id);
        $operator->restore();
        return back()->with('success', "Operator {$operator->nama_operator} berhasil dipulihkan.");
    }
}
