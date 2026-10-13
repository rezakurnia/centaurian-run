<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens,SoftDeletes;

    protected $fillable = ['username', 'password', 'email', 'role', 'last_login'];

    protected $hidden = ['password'];

    public function registrationsVerified(): HasMany
    {
        return $this->hasMany(Registration::class, 'verified_by');
    }

    public function resultsScanned(): HasMany
    {
        return $this->hasMany(Result::class, 'scanned_by');
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class, 'scanned_by');
    }

    public function contentsUpdated(): HasMany
    {
        return $this->hasMany(Content::class, 'updated_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
