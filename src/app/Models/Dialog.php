<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dialog extends Model
{
    protected $fillable = [
        'telegram_id',
        'name',
        'type',
        'username',
        'last_message',
        'last_message_date'
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
