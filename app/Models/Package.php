<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = ['name', 'price', 'description'];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
