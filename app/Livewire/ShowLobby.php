<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ZalimKasaba\Lobby;
use App\Models\ZalimKasaba\Player;
use App\Enums\ZalimKasaba\GameState;
use Illuminate\Support\Facades\Auth;
use App\Enums\ZalimKasaba\LobbyStatus;
use App\Traits\ZalimKasaba\ChatManager;
use App\Traits\ZalimKasaba\VoteManager;
use App\Traits\ZalimKasaba\StateManager;
use App\Traits\ZalimKasaba\PlayerManager;
use App\Traits\ZalimKasaba\PlayerActionsManager;

class ShowLobby extends Component
{
    use StateManager, ChatManager, PlayerManager, VoteManager, PlayerActionsManager;

    public Lobby $lobby;

    public string $gameTitle = '';

    public Player $currentPlayer;
    public ?Player $hostPlayer;

    public bool $judgeModal;

    public function mount(Lobby $lobby)
    {
        $this->lobby = $lobby;

        if ($lobby->status === LobbyStatus::WAITING_HOST && $lobby->host_id !== Auth::id()) {
            return redirect()->route('lobbies')->warning('Oyun yöneticisi aktif değil.');
        }

        $this->gameTitle = $this->setGameTitle($lobby);

        $this->currentPlayer = $this->initializeCurrentPlayer();

        $this->hostPlayer = $lobby->players()->where('is_host', true)->first();

        $this->checkHostAvailability();

        if ($this->currentPlayer->wasRecentlyCreated) {
            $this->currentPlayer->update(['place' => $this->lobby->players()->max('place') + 1]);
            $this->sendSystemMessage($this->lobby, $this->currentPlayer->user->username . ' oyuna katıldı.');
        }

        $this->setJudgeModalState();
    }

    private function checkHostAvailability()
    {
        if (!$this->hostPlayer) {
            return redirect()->route('lobbies')->warning('Oyun yöneticisi aktif değil.');
        }
    }

    private function assignRoles(Lobby $lobby)
    {
        $players = $lobby->players;

        $availableRoles = $lobby->roles->shuffle();

        // Assign roles to players
        foreach ($players as $player) {
            $role = $availableRoles->pop();
            $player->update([
                'game_role_id' => $role->id,
                'ability_uses' => $role->ability_limit,
            ]);
        }
    }

    /**
     * Sets the game title based on the lobby state
     * @return string
     */
    private function setGameTitle(): string
    {
        return match ($this->lobby->state) {
            GameState::LOBBY => '🏟️ Lobi',
            GameState::PREPARATION => '🎲 Hazırlık',
            GameState::DAY => "🌞 {$this->lobby->day_count}. Gün",
            GameState::VOTING => '🗳️ Oylama',
            GameState::DEFENSE => '🛡️ Savunma',
            GameState::JUDGMENT => "👨‍⚖️ Yargı ({$this->lobby->accused?->user->username})",
            GameState::LAST_WORDS => '🗣️ Son Sözler',
            GameState::NIGHT => "🌙 {$this->lobby->day_count}. Gece",
            GameState::REVEAL => '🔍 Açıklama',
            GameState::GAME_OVER => '🏁 Oyun Bitti',
        };
    }

    public function getListeners()
    {
        return [
            "echo-presence:zalim-kasaba-lobby.{$this->lobby->id},.game.new.message" => 'handleNewChatMessage',
            "echo-presence:zalim-kasaba-lobby.{$this->lobby->id},.game.state.updated" => 'handleGameStateUpdated',
            "echo-presence:zalim-kasaba-lobby.{$this->lobby->id},.game.player.kicked" => 'handleKick',
            "echo-presence:zalim-kasaba-lobby.{$this->lobby->id},here" => 'handleUsersHere',
            "echo-presence:zalim-kasaba-lobby.{$this->lobby->id},joining" => 'handleUserJoined',
            "echo-presence:zalim-kasaba-lobby.{$this->lobby->id},leaving" => 'handleUserLeft',
        ];
    }

    public function handleGameStateUpdated($payload)
    {
        $this->setJudgeModalState();
        $this->gameTitle = $this->setGameTitle($this->lobby);
    }

    public function startGame()
    {
        if ($this->lobby->state !== GameState::LOBBY || !$this->currentPlayer->is_host || $this->lobby->status !== LobbyStatus::ACTIVE) {
            return;
        }

        $this->randomizePlayerPlaces();

        $this->nextState();
    }

    public function goToNextGameState()
    {
        if (!$this->currentPlayer->is_host) {
            return;
        }

        $this->runStateExitEvents($this->lobby->state);

        // If lobby countdown_end is still in the future, do not proceed
        // This is a protection against front-end manipulation
        if ($this->lobby->countdown_end && $this->lobby->countdown_end->isFuture()) {
            return;
        }

        $this->nextState();
    }

    public function render()
    {
        if (!$this->currentPlayer->is_online) {
            $this->currentPlayer->update(['is_online' => true]);
        }
        return view('livewire.show-lobby')->title($this->lobby->name . ' - Zalim Kasaba');
    }
}
