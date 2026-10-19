<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLocation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', 'latitude', 'longitude',
        'city', 'state', 'country', 'postcode',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude'  => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Get the user that owns the location.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the location label.
     *
     * @return Attribute
     */
    protected function label(): Attribute
    {
        return Attribute::get(
            fn () => collect([$this->city, $this->state, $this->country, $this->postcode])->filter()->implode(', ')
        );
    }
}
