<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'registration_number', 'sequence_number', 'barcode',
        'participant_id', 'event_id', 'category_id', 'package_id',
        'registration_status', 'payment_status',
        'snap_token', 'snap_redirect_url', 'payment_order_id', 'payment_expires_at', 'payment_expired_at',
        'payment_method', 'paid_at',
        'verified_by', 'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'paid_at' => 'datetime',
        'payment_expires_at' => 'datetime',
        'payment_expired_at' => 'datetime',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function result(): HasOne
    {
        return $this->hasOne(Result::class);
    }
}
