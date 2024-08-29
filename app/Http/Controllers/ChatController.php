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
    public function index(Request $request)
    {
        //dd($request);
        return Inertia::render('Chat/Index', [
            'chats' => $this->get(),
        ]);
    }
    public function getChats()
    {
        return response()->json($this->get());
    }
    private function get()
    {
        $chats = ChatData::collect(auth()->user()->chats())
            ->map(function ($chat) {
                return [
                    'chat' => $chat,
                    'lastMessageCreatedAt' => $chat->lastMessage->createdAt,
                ];
            })
            ->sortByDesc('lastMessageCreatedAt') // сортировка по дате создания сообщения
            ->take(100) // взять последние 100
            ->pluck('chat'); // извлечение самих чатов

        //картинка пользователей
        $chats->each(function ($chat) {
            $userId = $chat->user->id;
            $userDir = Storage::disk('public')->files('/img/public/users/' . $userId);
            $userIcon = count($userDir) ? '/storage/' . $userDir[0] : '/storage/img/public/users/default.webp';
            $chat->user->icon = $userIcon;
        });

        return $chats;
    }
    public function readMessages(Request $request)
    {
        $chat = Chat::query()->find($request->chatID);
        if (auth()->id() == $request->userID)
            $chat->messages()->where('is_read', '=', false)->update(['is_read' => true]);
    }
    public function show(Request $request)
    {
        $chat = Chat::query()->find($request->chatID);

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

        /*$chatData = ChatData::from($chat);
        $chatData->user->lastMessage = MessageData::from(Message::query()->where('chat_id', '=', $chat->id)->latest()->first());
        if (!$chatData->user->lastMessage->isRead && $chatData->user->lastMessage->user->id == $userId) {
            $chat->messages()->where('is_read', '=', false)->update(['is_read' => true]);
        }*/

        if (!$chat->hasAccess(auth()->id()))
            abort(403);

        return response()->json(
            [
                'chatId' => $chat->id,
                'companion' => $companion,
                'messages' => MessageData::collect($chat->messages)->take(1000)
            ]
        );

        /*return Inertia::render('Chat/Show', [
            'chatId' => $chat->id,
            'companion' => $companion,
            'messages' => MessageData::collect($chat->messages),
        ]);*/
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

        return redirect()->route('chats.index', $chat);
    }
}
