<?php

namespace App\Traits\ZalimKasaba;

use Masmerise\Toaster\Toaster;
use Illuminate\Support\Collection;
use App\Enums\ZalimKasaba\GameState;
use App\Enums\ZalimKasaba\PlayerRole;
use App\Enums\ZalimKasaba\FinalVoteType;

trait StateExitEvents
{
    use ChatManager, PlayerActionsManager, VoteManager;

    private function exitLobby()
    {
        $this->validateEvent(GameState::LOBBY);
    }

    private function exitDay()
    {
        $this->validateEvent(GameState::DAY);

        if ($this->lobby->day_count === 1) {
            $this->nextState(GameState::NIGHT);
            return;
        }
    }

    private function exitVoting()
    {
        $this->validateEvent(GameState::VOTING);

        $accusedPlayerId = $this->getAccusedPlayer();
        if (!$accusedPlayerId) {
            $this->sendSystemMessage($this->lobby, 'Oy birliği sağlanamadı. Oylama bitti.');
            $this->nextState(GameState::NIGHT);
            return;
        }
        $this->lobby->update(['accused_id' => $accusedPlayerId]);
    }

    private function exitDefense()
    {
        $this->validateEvent(GameState::DEFENSE);
    }

    private function exitJudgment()
    {
        $this->validateEvent(GameState::JUDGMENT);

        $judgmentResult = $this->calculateFinalVotes();

        $this->sendSystemMessage(
            $this->lobby,
            "Yargılama süreci sona erdi. {$judgmentResult['guilty']} suçlu, {$judgmentResult['inno']} masum oy aldı. {$judgmentResult['abstain']} oy çekimser kaldı."
        );

        if ($judgmentResult['type'] === FinalVoteType::GUILTY->value) {
            // Guilty verdict: Transition to LAST_WORDS and send a message
            $accused = $this->lobby->accused;
            if ($accused) {
                $this->sendSystemMessage($this->lobby, "{$accused->user->username} suçlu bulundu. Son sözlerini söylemesi için süre tanınıyor.");
            }
            $this->nextState(GameState::LAST_WORDS);
        } else {
            // Innocent verdict: Proceed as in your original logic
            $this->lobby->update(['accused_id' => null]);
            if ($this->lobby->available_trials > 0) {
                $this->sendSystemMessage($this->lobby, 'Mahkeme karar veremedi. Yeni bir oylama başlatılıyor.');
                $this->nextState(GameState::VOTING);
            } else {
                $this->sendSystemMessage($this->lobby, 'Oylama hakkınız kalmadı. Geceye geçiliyor.');
                $this->nextState(GameState::NIGHT);
            }
        }
    }

    private function exitLastWords()
    {
        $this->validateEvent(GameState::LAST_WORDS);

        $accused = $this->lobby->accused;
        $username = $accused->user->username;
        $roleName = $accused->role->name;
        $roleIcon = $accused->role->icon;
        if ($accused) {
            //$this->killPlayer($accused, $accused->role->enum === PlayerRole::JESTER);
            $this->sendSystemMessage(
                $this->lobby,
                "{$username} kasaba tarafından idam edildi. Oyuncunun rolü: {$roleIcon} {$roleName}."
            );
        }

        $this->lobby->update(['accused_id' => null]);
    }

    private function exitNight()
    {
        $this->validateEvent(GameState::NIGHT);

        // Fetch all actions and players once for efficiency
        $actions = $this->lobby->actions()->get();
        $players = $this->lobby->players()->get()->keyBy('id');

        $this->informAbiltyWasted($actions, $players);
    }

    private function exitReveal()
    {
        $this->validateEvent(GameState::REVEAL);
    }

    private function exitPreparation()
    {
        $this->validateEvent(GameState::PREPARATION);
    }

    // FUNCTIONS START

    /**
     * Validate the event before the exit event is fired.
     * @param GameState $currentState
     * @return bool
     */
    private function validateEvent(GameState $currentState): bool
    {
        if (!$this->currentPlayer->is_host) return false;
        if ($this->lobby->state !== $currentState) return false;
        return true;
    }

    /**
     * Send a message to players who did not use their ability during the night phase.
     * @param Collection $actions
     * @param Collection $players
     * @return void
     */
    private function informAbiltyWasted(Collection $actions, Collection $players): void
    {
        foreach ($players as $player) {
            // Check ability usage for living players with roles
            if ($player->is_alive) {
                $playerActions = $actions->where('actor_id', $player->id);
                if ($playerActions->count() === 0) {
                    $this->sendMessageToPlayer($player, 'Gece yeteneğinizi kullanmadınız.');
                } else {
                    if ($player->ability_uses > 0) {
                        $player->decrement('ability_uses');
                    }
                }
            }
        }
    }
}
