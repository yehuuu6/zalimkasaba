<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('zalim-kasaba-lobby.{lobbyId}', function (User $user, $lobbyId) {
    return $user->only('id', 'username');
});
