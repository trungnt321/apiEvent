<?php

use App\Http\Controllers\chatController;
use Illuminate\Support\Facades\Broadcast;
use App\Broadcasting\ChatChannel;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    // Logic kiểm tra xem người dùng có quyền tham gia vào nhóm chat không
    return true; // Ví dụ: Luôn cho phép tất cả người dùng tham gia kênh này
});

