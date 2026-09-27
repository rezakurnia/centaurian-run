<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Participant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'full_name', 'gender', 'birth_place', 'birth_date',
        'motivation', 'email', 'phone', 'email_sent_at',
    ];

    protected $casts = [
        'birth_date'    => 'date',
        'email_sent_at' => 'datetime',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(Result::class);
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }
}