<?php

namespace App\Livewire;

use App\Models\ZalimKasaba\Player;
use App\Models\ZalimKasaba\Lobby;
use App\Traits\ZalimKasaba\ChatManager;
use Illuminate\Support\Collection;
use Livewire\Component;

class ChatWindow extends Component
{
    use ChatManager;

    public Lobby $lobby;

    public string $message = '';

    public Collection $messages;

    public Player $currentPlayer;

    public function mount(Lobby $lobby, Player $currentPlayer)
    {
        $this->lobby = $lobby;
        $this->currentPlayer = $currentPlayer;
        $this->messages = $lobby->messages()->oldest()->limit(100)->get();
    }

    public function getListeners()
    {
        return [
            "echo-presence:zalim-kasaba-lobby.{$this->lobby->id},.game.new.message" => 'handleNewChatMessage',
        ];
    }

    public function handleNewChatMessage()
    {
        // Push the messages that are created in the last 10 seconds into the array.
        $lastMessages = $this->lobby->messages()->where('created_at', '>', now()->subSeconds(10))->get();
        $this->messages = $this->messages->merge($lastMessages);
        $this->dispatch('chat-message-received');
    }

    public function render()
    {
        return view('livewire.chat-window');
    }
}
