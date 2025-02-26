<?php

namespace App\Traits\ZalimKasaba;

use App\Models\ZalimKasaba\Lobby;
use App\Models\ZalimKasaba\Player;
use App\Enums\ZalimKasaba\GameState;
use App\Enums\ZalimKasaba\PlayerRole;
use App\Models\ZalimKasaba\GameRole;
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;

trait GameUtils
{
    /**
     * Initializes current player instance
     * @param Lobby $lobby
     * @return string
     */
    private function initializeCurrentPlayer(): Player
    {
        // Get the highest current place value for this lobby
        $highestPlace = $this->lobby->players()->max('place') ?? 0;

        $currentPlayer = $this->lobby->players()->updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'is_online' => true,
                'last_seen' => now(),
                'is_host' => $this->lobby->host_id === Auth::id(),
            ],
        );

        return $currentPlayer;
    }

    /**
     * Reorders the player places in the lobby
     * @param Lobby $lobby
     */
    private function reorderPlayerPlaces(Lobby $lobby)
    {
        $players = $lobby->players()->oldest()->get();
        $place = 1;

        foreach ($players as $player) {
            if ($player->place !== $place) {
                $player->update(['place' => $place]);
            }
            $place++;
        }
    }

    /**
     * Randomizes the place values of all players in the lobby.
     * @param Lobby $lobby The lobby whose players' places will be randomized.
     * @return void
     */
    private function randomizePlayerPlaces(Lobby $lobby)
    {
        // Get all players in the lobby
        $players = $lobby->players()->get();

        // Shuffle the players collection
        $shuffledPlayers = $players->shuffle();

        // Assign new place values starting from 1
        $place = 1;
        foreach ($shuffledPlayers as $player) {
            $player->update(['place' => $place]);
            $place++;
        }
    }

    /**
     * Sets the game title based on the lobby state
     * @param Lobby $lobby
     * @return string
     */
    private function setGameTitle(Lobby $lobby): string
    {
        return match ($lobby->state) {
            GameState::LOBBY => '🏟️ Lobi',
            GameState::PREPARATION => '🎲 Hazırlık',
            GameState::DAY => "🌞 $lobby->day_count. Gün",
            GameState::VOTING => '🗳️ Oylama',
            GameState::DEFENSE => '🛡️ Savunma',
            GameState::JUDGMENT => '👨‍⚖️ Yargı',
            GameState::NIGHT => "🌙 $lobby->day_count. Gece",
            GameState::REVEAL => '🔍 Açıklama',
            GameState::GAME_OVER => '🏁 Oyun Bitti',
        };
    }

    /**
     * Returns the action name of the player role for the button in the frontend
     * @param Player $player
     * @return string|null
     */
    public function getPlayerActionName(Player $player): string | null
    {
        $role = $player->role->enum->value;

        $playerActionName = match ($role) {
            'guard' => 'Sorgula',
            'godfather' => 'Öldür',
            'mafioso' => 'Öldür',
            'doctor' => 'Koru',
            'lookout' => 'Dikizle',
            'jester' => 'Lanetle',
            'hunter' => 'Vur',
            default => null
        };

        return $playerActionName;
    }

    private function assignRoles(Lobby $lobby)
    {
        $players = $lobby->players;

        $availableRoles = $lobby->roles->shuffle();

        // Assign roles to players
        foreach ($players as $player) {
            $role = $availableRoles->pop();
            $player->update(['game_role_id' => $role->id]);
        }
    }
}
