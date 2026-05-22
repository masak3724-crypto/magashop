<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = ['user_id', 'phone', 'address', 'city', 'postal_code', 'avatar'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function avatarUrl(): string
    {
        if ($this->avatar && file_exists(public_path('storage/'.$this->avatar))) {
            return asset('storage/'.$this->avatar);
        }

        return asset('images/avatar-default.svg');
    }
}
