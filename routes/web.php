<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\LobbiesList;
use App\Livewire\ShowLobby;
use App\Livewire\Auth\Pages\Login;
use App\Livewire\Auth\Pages\Register;
use App\Livewire\CreateLobby;

Route::middleware('guest')->group(function () {
    Route::get('/register', Register::class)->name('register');
    Route::get('/login', Login::class)->name('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/', LobbiesList::class)->name('lobbies');
    Route::get('/lobby/create', CreateLobby::class)->name('lobbies.create');
    Route::get('/lobby/{lobby:uuid}', ShowLobby::class)->name('lobbies.show');
    Route::delete('/logout', [Login::class, 'logout'])->middleware('auth')->name('logout');
});
