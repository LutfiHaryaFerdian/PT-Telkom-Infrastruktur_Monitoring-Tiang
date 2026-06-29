<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Area;
use App\Models\Sto;
use App\Models\JenisTiang;
use App\Models\KondisiTiang;
use App\Models\OperatorIsp;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Semua menggunakan updateOrCreate agar aman dijalankan berulang kali.
     */
    public function run(): void
    {
        // ============================================================
        // 1. USERS
        // ============================================================
        User::updateOrCreate(
            ['email' => 'admin@telkominf.com'],
            [
                'name'     => 'Admin',
                'role'     => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'teknisi@telkominf.com'],
            [
                'name'     => 'Teknisi',
                'role'     => 'teknisi',
                'password' => Hash::make('password'),
            ]
        );

        // ============================================================
        // 2. DISTRICTS
        // ============================================================
        $district = District::updateOrCreate(
            ['name' => 'Lampung'],
            ['name' => 'Lampung']
        );

        // ============================================================
        // 3. AREAS
        // ============================================================
        $area = Area::updateOrCreate(
            ['district_id' => $district->id, 'name' => 'Lampung'],
            ['district_id' => $district->id, 'name' => 'Lampung']
        );

        // ============================================================
        // 4. STOS
        // ============================================================
        Sto::updateOrCreate(
            ['kode' => 'KDT'],
            [
                'area_id' => $area->id,
                'kode'    => 'KDT',
                'nama'    => 'STO Kedaton',
            ]
        );

        // ============================================================
        // 5. JENIS TIANG
        // ============================================================
        JenisTiang::updateOrCreate(
            ['nama' => 'T-7-2 Seqment'],
            ['nama' => 'T-7-2 Seqment', 'keterangan' => 'Tiang beton 7 meter 2 segmen']
        );

        JenisTiang::updateOrCreate(
            ['nama' => 'T-7-3 Seqment'],
            ['nama' => 'T-7-3 Seqment', 'keterangan' => 'Tiang beton 7 meter 3 segmen']
        );

        // ============================================================
        // 6. KONDISI TIANG
        // ============================================================
        KondisiTiang::updateOrCreate(
            ['nama' => 'Baik. Cat OK'],
            ['nama' => 'Baik. Cat OK', 'level' => 'baik']
        );

        KondisiTiang::updateOrCreate(
            ['nama' => 'Baik. Cat NOK'],
            ['nama' => 'Baik. Cat NOK', 'level' => 'perlu_perhatian']
        );

        // ============================================================
        // 7. OPERATOR ISP (is_predefined = true)
        // ============================================================
        $predefinedOperators = [
            'BIZNET',
            'ICON+',
            'IFORTE',
            'XL',
            'MyRepublic',
            'PGNCOM',
            'Lintas Arta',
            'Inforte',
        ];

        foreach ($predefinedOperators as $nama) {
            OperatorIsp::updateOrCreate(
                ['nama_operator' => $nama],
                ['nama_operator' => $nama, 'is_predefined' => true]
            );
        }

        $this->command->info('✅ Seeder selesai dijalankan.');
        $this->command->table(
            ['Entitas', 'Keterangan'],
            [
                ['Users', '2 user (admin@telkominf.com, teknisi@telkominf.com)'],
                ['Districts', '1 district (Lampung)'],
                ['Areas', '1 area (Lampung)'],
                ['Stos', '1 STO (KDT - Kedaton)'],
                ['Jenis Tiang', '2 jenis (T-7-2, T-7-3)'],
                ['Kondisi Tiang', '2 kondisi (Baik OK, Baik NOK)'],
                ['Operator ISP', count($predefinedOperators) . ' operator predefined'],
            ]
        );
    }
}
