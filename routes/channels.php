<?php

use App\Broadcasting\ChatChannel;
use App\Broadcasting\UserChatsChannel;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('account-update', function () {
    return true;
});
Broadcast::channel('Chat.{chat}', ChatChannel::class);
Broadcast::channel('UserChats.{user}', UserChatsChannel::class);
