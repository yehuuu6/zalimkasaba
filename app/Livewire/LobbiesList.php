<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ZalimKasaba\Lobby;
use Livewire\Attributes\Title;

#[Title('Oyun Lobileri - Zalim Kasaba')]
class LobbiesList extends Component
{
    public function render()
    {
        return view('livewire.lobbies-list', [
            'lobbies' => Lobby::latest()->get()
        ]);
    }
}
