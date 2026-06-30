<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $fillable = ['district_id', 'name'];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function stos(): HasMany
    {
        return $this->hasMany(Sto::class);
    }

    public function getTiangCountAttribute(): int
    {
        return \App\Models\TiangTelekomunikasi::whereHas('sto', function ($q) {
            $q->where('area_id', $this->id);
        })->count();
    }
}
