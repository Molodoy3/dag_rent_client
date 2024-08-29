<?php

namespace App\Data;

use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class MessageData extends Data
{
    public function __construct(
        public int $id,
        public ?string $message,
        public ?string $createdAt,
        public ?User $user,
        public bool $isOwn,
        public bool $isRead
    ) {
    }
    public static function fromModel(Message $message): static
    {
        //dd(User::query()->find($message->sender->id));
        return new static(
            id: $message->id,
            message: $message->message,
            createdAt: Carbon::parse($message->created_at)->isToday()
                ? Carbon::parse($message->created_at)->format('H:i')
                : Carbon::parse($message->created_at)->format('d.m H:i'),
            user: $message->sender,
            isOwn: $message->sender_id === auth()->id(),
            isRead: $message->is_read
        );
    }
}
