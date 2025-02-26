<?php

namespace App\Traits\ZalimKasaba;

use App\Models\ZalimKasaba\Player;
use Illuminate\Support\Collection;
use App\Enums\ZalimKasaba\GameState;
use App\Enums\ZalimKasaba\ActionType;
use App\Enums\ZalimKasaba\PlayerRole;
use App\Enums\ZalimKasaba\ChatMessageType;

trait PlayerActionsManager
{
    /**
     * Gets killed players by fetching heal and kill actions.
     * @param Collection $action The actions performed by players.
     * @return array The players who are murdered during the night.
     */
    private function getKilledPlayers(Collection $actions): array
    {
        $healedPlayers = $actions->where('action_type', 'heal')
            ->pluck('target_id')
            ->toArray();

        // Step 2: Collect killed players
        $attackTypes = [ActionType::ORDER, ActionType::KILL, ActionType::SHOOT, ActionType::HAUNT];
        $attackedPlayers = $actions->whereIn('action_type', $attackTypes)
            ->pluck('target_id')
            ->toArray();

        // Return the players who are attacked but not healed (killed)
        return array_unique(array_diff($attackedPlayers, $healedPlayers));
    }

    public function performPlayerAction(Player $targetPlayer)
    {
        if ($this->lobby->state !== GameState::NIGHT) {
            return;
        }

        $wantedAction = $this->getCurrentPlayerAction();
        $msg = $this->getActionCompleteMessage($targetPlayer);

        if (!$this->currentPlayer->is_alive || $wantedAction === null) {
            return;
        }

        $existingAction = $this->lobby->actions()->where([
            'actor_id' => $this->currentPlayer->id,
            'action_type' => $wantedAction,
        ])->first();

        if ($existingAction) {
            $existingAction->delete();
            if ($existingAction->target_id === $targetPlayer->id) {
                $this->sendMessageToPlayer($this->currentPlayer, "Önceki eyleminizi iptal ettiniz.");
                return;
            }
        }

        $this->lobby->actions()->create([
            'actor_id' => $this->currentPlayer->id,
            'target_id' => $targetPlayer->id,
            'action_type' => $wantedAction,
        ]);

        $this->sendMessageToPlayer($this->currentPlayer, $msg);
    }

    /**
     * Send players a message about the action they have taken.
     * @param Player $targetPlayer The player who is targeted by the action.
     * @return string The message to be sent to the player.
     */
    private function getActionCompleteMessage(Player $targetPlayer): string
    {
        return match ($this->currentPlayer->role->enum) {
            PlayerRole::GODFATHER => "{$targetPlayer->user->username} adlı oyuncuyu öldürmesi için Memati'ye emir verdin.",
            PlayerRole::MAFIOSO => "{$targetPlayer->user->username} adlı oyuncuyu öldürme kararı aldın.",
            PlayerRole::DOCTOR => "{$targetPlayer->user->username} adlı oyuncuyu koruma kararı aldın.",
            PlayerRole::GUARD => "{$targetPlayer->user->username} adlı oyuncuya GBT sorgusu yapmaya karar verdin.",
            PlayerRole::LOOKOUT => "{$targetPlayer->user->username} adlı oyuncunun evini dikizlemeye karar verdin.",
            PlayerRole::HUNTER => "{$targetPlayer->user->username} adlı oyuncuyu vurma kararı aldın.",
            PlayerRole::JESTER => "{$targetPlayer->user->username} adlı oyuncuyu lanetlemeye karar verdin.",
            default => '',
        };
    }

    /**
     * Gets the action type that the current player can perform based on their role.
     * @return ActionType|null The action type that the player can perform.
     */
    private function getCurrentPlayerAction(): ActionType | null
    {
        return match ($this->currentPlayer->role->enum) {
            PlayerRole::GODFATHER => ActionType::ORDER,
            PlayerRole::MAFIOSO => ActionType::KILL,
            PlayerRole::DOCTOR => ActionType::HEAL,
            PlayerRole::GUARD => ActionType::INTERROGATE,
            PlayerRole::LOOKOUT => ActionType::WATCH,
            PlayerRole::HUNTER => ActionType::SHOOT,
            PlayerRole::JESTER => ActionType::HAUNT,
            default => null,
        };
    }

    /**
     * Checks if the current player has performed an action on the target player.
     * @param Player $targetPlayer The player who is targeted by the action.
     * @return bool True if the player has performed an action, false otherwise.
     */
    public function hasPerformedAction(Player $targetPlayer): bool
    {
        return $this->lobby->actions()->where([
            'actor_id' => $this->currentPlayer->id,
            'target_id' => $targetPlayer->id,
        ])->exists();
    }

    /**
     * Applies night actions to the players based on the current game state.
     * @param GameState $currentState The current state of the game.
     * @return void
     */
    private function applyNightActionsToPlayer(GameState $currentState)
    {
        // Ensure it’s night phase and only the host processes
        if ($currentState !== GameState::NIGHT) return;
        if (!$this->currentPlayer->is_host) return;

        // Fetch all actions and players once for efficiency
        $actions = $this->lobby->actions()->get();
        $players = $this->lobby->players()->get()->keyBy('id');

        // Process each player’s night actions
        foreach ($players as $player) {
            // Check ability usage for living players with roles
            if ($player->is_alive) {
                $playerActions = $actions->where('actor_id', $player->id);
                if ($playerActions->count() === 0) {
                    $this->sendMessageToPlayer($player, 'Gece yeteneğinizi kullanmadınız.');
                }
            }

            // Check actions targeting this player
            $healActions = $actions->where('target_id', $player->id)
                ->where('action_type', ActionType::HEAL);
            $attackActions = $actions->whereIn('action_type', [ActionType::ORDER, ActionType::KILL, ActionType::SHOOT, ActionType::HAUNT])
                ->where('target_id', $player->id);

            $wasHealed = $healActions->count() > 0;
            $wasAttacked = $attackActions->count() > 0;

            if ($wasAttacked) {
                if ($wasHealed) {
                    // Player was attacked but healed
                    $healAction = $healActions->first();
                    $healer = $players->get($healAction->actor_id);
                    $attackAction = $attackActions->first();
                    $attacker = $players->get($attackAction->actor_id);

                    $this->sendMessageToPlayer($attacker, 'Hedefinize saldırdınız, ancak biri onu geri hayata döndürdü!', ChatMessageType::WARNING);
                    $this->sendMessageToPlayer($healer, 'Hedefiniz saldırıya uğradı, ancak onu kurtardınız!', ChatMessageType::SUCCESS);
                    $this->sendMessageToPlayer($player, 'Biri evine girip sana saldırdı, ama bir doktor seni kurtardı!', ChatMessageType::SUCCESS);
                } else {
                    // Player was attacked and not healed
                    $this->sendMessageToPlayer($player, 'Biri evinize girdi, öldürüldünüz!', ChatMessageType::WARNING);
                }
            }
        }
    }
}
