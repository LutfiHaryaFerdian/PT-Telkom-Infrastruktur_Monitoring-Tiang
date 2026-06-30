<?php

namespace App\Policies;

use App\Models\TiangTelekomunikasi;
use App\Models\User;

class TiangPolicy
{
    /**
     * before(): Admin lolos SEMUA pengecekan policy.
     * Method lain hanya dieksekusi jika bukan admin.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->role === 'admin') {
            return true; // admin bypass semua policy
        }

        return null; // lanjut ke method policy spesifik
    }

    /**
     * create: admin & teknisi bisa membuat tiang baru.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'teknisi']);
    }

    /**
     * update: admin & teknisi bisa edit data tiang.
     */
    public function update(User $user, TiangTelekomunikasi $tiang): bool
    {
        return in_array($user->role, ['admin', 'teknisi']);
    }

    /**
     * delete: hanya admin (sudah ditangani di before()).
     */
    public function delete(User $user, TiangTelekomunikasi $tiang): bool
    {
        return false;
    }

    /**
     * restore: hanya admin (sudah ditangani di before()).
     */
    public function restore(User $user, TiangTelekomunikasi $tiang): bool
    {
        return false;
    }

    /**
     * verify: hanya admin (sudah ditangani di before()).
     */
    public function verify(User $user, TiangTelekomunikasi $tiang): bool
    {
        return false;
    }

    /**
     * updateKode: hanya admin (sudah ditangani di before()).
     */
    public function updateKode(User $user, TiangTelekomunikasi $tiang): bool
    {
        return false;
    }
}
