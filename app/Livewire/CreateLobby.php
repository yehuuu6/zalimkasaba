<?php

namespace App\Livewire;

use Masmerise\Toaster\Toaster;
use App\Models\ZalimKasaba\Lobby;
use App\Models\ZalimKasaba\GameRole;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateLobby extends Component
{
    public string $lobbyName;
    public array $selectedRoles = [];
    public Collection $gameRoles;

    public function mount()
    {
        $this->gameRoles = GameRole::all();
    }

    public function createLobby()
    {
        // Only get the ids of the selected roles
        $roleIds = collect($this->selectedRoles)->map(fn($role) => $role['id']);

        $lobby = Lobby::create([
            'host_id' => Auth::id(),
            'name' => $this->lobbyName,
            'max_players' => $roleIds->count(),
        ]);

        $lobby->roles()->attach($roleIds);

        return redirect()->route('lobbies.show', $lobby->uuid)->success('Lobi başarıyla oluşturuldu.');
    }

    public function render()
    {
        return view('livewire.create-lobby');
    }
}
