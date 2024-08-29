<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'sender_id',
        'message',
        'is_read'
    ];
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($message) {
            // Установим is_read в false по умолчанию
            $message->is_read = false;
        });
    }
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id')->withDefault();
    }
}
