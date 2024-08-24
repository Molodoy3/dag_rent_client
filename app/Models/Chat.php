<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'initiator_id',
        'recipient_id',
    ];
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
    public function user()
    {
        return $this->initiator_id === auth()->id()
            ? User::query()->find($this->recipient_id)
            : User::query()->find($this->initiator_id);
    }
    //для проверки права просматривать чат
    public function hasAccess($userId):bool {
        if ($userId == null)
            return false;
        return $this->initiator_id === $userId || $this->recipient_id === $userId || User::query()->where('id', $userId)->role_id === 1;
    }
}
