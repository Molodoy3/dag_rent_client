<?php

namespace App\Data;

use App\Models\Chat;
use App\Models\Message;
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
        public MessageData $lastMessage
    ) {
    }
    public static function fromModel(Chat $chat): static
    {

        return new static(
            id: $chat->id,
            //totalMessages: $chat->messages()->count(),
            user: $chat->user(),
            lastMessage: Message::where('chat_id', '=', $chat->id)->exists() ?
                MessageData::from(Message::query()->where('chat_id', '=', $chat->id)->latest()->first())
                :
                MessageData::from(['id'=>1,'chat_id' => $chat->id,'sender_id' => $chat->user()->id,'message' => '','isRead' => true, 'isOwn' => false, 'created_at'=>now(), 'updated_at'=>now()])
        );
    }
}
