<?php

use App\Broadcasting\ChatChannel;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('account-update', function () {
    return true;
});
Broadcast::channel('Chat.{chat}', ChatChannel::class);
