<?php

namespace App\Data;

use App\Models\Chat;
use App\Models\User;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ChatData extends Data
{
    public function __construct(
        public int $id,
        //public int $totalMessages,
        public User $user,
    ) {
    }
    public static function fromModel(Chat $chat): static
    {

        return new static(
            id: $chat->id,
            //totalMessages: $chat->messages()->count(),
            user: $chat->user(),
        );
    }
}
