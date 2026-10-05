<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    /* @var array */
    protected $fillable = [
        'user_id',
        'device_id',
        'device_type',
        'token_id',
        'last_ip',
        'user_agent',
        'last_login_at',
        'last_logout_at',
    ];

    /* @var array */
    protected $casts = [
        'last_login_at' => 'datetime',
        'last_logout_at' => 'datetime',
    ];

    /**
     * Get the user that owns the device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
