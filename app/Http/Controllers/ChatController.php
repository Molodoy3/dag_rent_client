<?php

namespace App\Http\Controllers;

use App\Data\ChatData;
use App\Data\MessageData;
use App\Data\SendMessageData;
use App\Data\UserData;
use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Statistic;
use App\Models\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index()
    {
        $chats = ChatData::collect(auth()->user()->chats());
        //картинка пользователей
        $chats->each(function ($chat) {
            $userId = $chat->user->id;
            $userDir = Storage::disk('public')->files('/img/public/users/' . $userId);
            $userIcon = count($userDir) ? '/storage/' . $userDir[0] : '/storage/img/public/users/default.webp';
            $chat->user->icon = $userIcon;

            $chat->user->lastMessage = MessageData::from(Message::query()->where('chat_id', '=', $chat->id)->latest()->first());
        });

        return Inertia::render('Chat/Index', [
            'chats' => $chats,
        ]);
    }

    public function show(Chat $chat)
    {
        $companion = User::with('accounts')->find($chat->user()->id);
        //кол-во продаж пользователя
        $userAccounts = $companion->accounts;
        $countSales = $userAccounts->pluck('id');
        $companion->countSales = Statistic::whereIn('account_id', $countSales)->count();

        //картинка пользователя
        $userId = $companion->id;
        $userDir = Storage::disk('public')->files('/img/public/users/' . $userId);
        $userIcon = count($userDir) ? '/storage/' . $userDir[0] : '/storage/img/public/users/default.webp';
        $companion->icon = $userIcon;


        if (!$chat->hasAccess(auth()->id()))
            abort(403);
        return Inertia::render('Chat/Show', [
            'chatId' => $chat->id,
            'companion' => $companion,
            'messages' => MessageData::collect($chat->messages),
        ]);
    }

    public function message(Chat $chat, SendMessageData $data)
    {
        $message = $chat->messages()->create([
            'sender_id' => auth()->id(),
            'message' => $data->message,
        ]);

        event(
            new MessageSent(
                data: ChatData::from($chat),
                message: MessageData::from($message)
            )
        );

        return redirect()->route('chats.show', $chat);
    }
}
