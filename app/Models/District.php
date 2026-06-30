<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class District extends Model
{
    protected $fillable = ['name'];

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function stos(): HasManyThrough
    {
        return $this->hasManyThrough(Sto::class, Area::class);
    }

    public function getTiangCountAttribute(): int
    {
        return \App\Models\TiangTelekomunikasi::whereHas('sto.area', function ($q) {
            $q->where('district_id', $this->id);
        })->count();
    }
}
