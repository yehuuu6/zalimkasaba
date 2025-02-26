<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ZalimKasaba\Lobby;
use App\Models\ZalimKasaba\Player;
use App\Enums\ZalimKasaba\GameState;
use Illuminate\Support\Facades\Auth;
use App\Traits\ZalimKasaba\GameUtils;
use App\Enums\ZalimKasaba\LobbyStatus;
use App\Traits\ZalimKasaba\ChatManager;
use App\Traits\ZalimKasaba\VoteManager;
use App\Traits\ZalimKasaba\StateManager;
use App\Traits\ZalimKasaba\PlayerManager;
use App\Events\ZalimKasaba\KickPlayerEvent;
use App\Traits\ZalimKasaba\PlayerActionsManager;

class ShowLobby extends Component
{
    use GameUtils, StateManager, ChatManager, PlayerManager, VoteManager, PlayerActionsManager;

    public Lobby $lobby;

    public string $gameTitle = '';

    public Player $currentPlayer;
    public ?Player $hostPlayer;

    public function mount(Lobby $lobby)
    {
        $this->lobby = $lobby;

        if ($lobby->status === LobbyStatus::WAITING_HOST && $lobby->host_id !== Auth::id()) {
            return redirect()->route('lobbies')->warning('Oyun yöneticisi aktif değil.');
        }

        $this->gameTitle = $this->setGameTitle($lobby);

        $this->currentPlayer = $this->initializeCurrentPlayer($lobby);

        $this->hostPlayer = $lobby->players()->where('is_host', true)->first();

        $this->checkHostAvailability();

        if ($this->currentPlayer->wasRecentlyCreated) {
            $this->currentPlayer->update(['place' => $this->lobby->players()->max('place') + 1]);
            $this->sendSystemMessage($this->lobby, $this->currentPlayer->user->username . ' oyuna katıldı.');
        }
    }

    private function checkHostAvailability()
    {
        if (!$this->hostPlayer) {
            return redirect()->route('lobbies')->warning('Oyun yöneticisi aktif değil.');
        }
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

    public function handleKick($payload)
    {
        if ($payload['playerId'] === $this->currentPlayer->id) {
            $this->currentPlayer->delete();
            return redirect()->route('lobbies')->warning('Yönetici tarafından oyundan atıldınız.');
        }

        if (!$this->currentPlayer->is_host) return;

        $this->reorderPlayerPlaces($this->lobby);
    }

    public function kickPlayer(Player $player)
    {
        if (!$this->currentPlayer->is_host) return;
        broadcast(new KickPlayerEvent($this->lobby->id, $player->id));
        if (!$player->is_online) {
            $player->delete();
        }
        $this->sendSystemMessage($this->lobby, $player->user->username . ' yönetici tarafından oyundan atıldı.');
    }

    public function handleGameStateUpdated($payload)
    {
        $this->gameTitle = $this->setGameTitle($this->lobby);
    }

    public function startGame()
    {
        if ($this->lobby->state !== GameState::LOBBY || !$this->currentPlayer->is_host || $this->lobby->status !== LobbyStatus::ACTIVE) {
            return;
        }

        $this->randomizePlayerPlaces($this->lobby);

        $this->nextState();
    }

    public function goToNextGameState()
    {
        $this->applyNightActionsToPlayer($this->lobby->state);

        if (!$this->currentPlayer->is_host) {
            return;
        }

        // If lobby countdown_end is still in the future, do not proceed
        // This is a protection against front-end manipulation
        if ($this->lobby->countdown_end && $this->lobby->countdown_end->isFuture()) {
            return;
        }

        $currentState = $this->lobby->state;

        switch ($currentState) {
            case GameState::DAY:
                if ($this->lobby->day_count === 1) {
                    $this->nextState(GameState::NIGHT);
                    return;
                }
                break;
            case GameState::VOTING:
                $accusedPlayerId = $this->getAccusedPlayer();
                if (!$accusedPlayerId) {
                    $this->sendSystemMessage($this->lobby, 'Oy birliği sağlanamadı. Oylama bitti.');
                    $this->nextState(GameState::NIGHT);
                    return;
                }
                $this->lobby->update(['accused_id' => $accusedPlayerId]);
                break;
            case GameState::JUDGMENT:
                if ($this->lobby->available_trials > 0) {
                    $this->lobby->update(['accused_id' => null]);
                    $this->sendSystemMessage($this->lobby, 'Mahkeme karar veremedi. Yeni bir oylama başlatılıyor.');
                    $this->nextState(GameState::VOTING);
                    return;
                }
                break;
            default:
                break;
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
