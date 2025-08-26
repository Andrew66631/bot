<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'dialog_id',
        'telegram_message_id',
        'message',
        'is_outgoing',
        'message_date'
    ];

    public function dialog(): BelongsTo
    {
        return $this->belongsTo(Dialog::class);
    }
}
