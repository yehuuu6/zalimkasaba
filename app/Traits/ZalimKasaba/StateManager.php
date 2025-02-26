<?php

namespace App\Traits\ZalimKasaba;

use App\Enums\ZalimKasaba\GameState;
use App\Events\ZalimKasaba\GameStateUpdated;

trait StateManager
{
    use StateEvents;

    /**
     * Changes the state of the lobby to the next state.
     * @param GameState $override If provided, the state will be set to this value.
     * @return void
     */
    private function nextState(GameState $override = null): void
    {
        if ($this->lobby->state->isFinal()) return;

        // Determine the next state: use override if provided, otherwise use default next state
        $nextState = $override ?? $this->lobby->state->next();

        $currentDate = $this->lobby->day_count;

        if ($nextState->value === GameState::DAY->value) {
            $this->lobby->update(['day_count' => $currentDate + 1]);
        }

        $this->lobby->update(['state' => $nextState]);

        // In seconds
        $oldTimerValues = [
            GameState::PREPARATION->value => 15,
            GameState::DAY->value => 90,
            GameState::VOTING->value => 30,
            GameState::DEFENSE->value => 30,
            GameState::JUDGMENT->value => 15,
            GameState::NIGHT->value => 60,
            GameState::REVEAL->value => 10,
        ];

        $timerValues = [
            GameState::PREPARATION->value => 15,
            GameState::DAY->value => 15,
            GameState::VOTING->value => 5,
            GameState::DEFENSE->value => 20,
            GameState::JUDGMENT->value => 15,
            GameState::NIGHT->value => 35,
            GameState::REVEAL->value => 5,
        ];

        // If the next state is in the timerValues array, set the countdown_end
        if (array_key_exists($nextState->value, $timerValues)) {
            $this->lobby->update(['countdown_start' => now(), 'countdown_end' => now()->addSeconds($timerValues[$nextState->value])]);
        }

        $this->runStateEvents($nextState);

        // Broadcast the updated state to all users in the lobby.
        broadcast(new GameStateUpdated($this->lobby->id, $nextState));
    }

    /**
     * Execute events when the given state is entered.
     * @param GameState $state
     * @return void
     */
    private function runStateEvents(GameState $state): void
    {
        match ($state) {
            GameState::DAY => $this->dayStateEvents(),
            GameState::VOTING => $this->voteStateEvents(),
            GameState::DEFENSE => $this->defenseStateEvents(),
            GameState::JUDGMENT => $this->judgmentStateEvents(),
            GameState::NIGHT => $this->nightStateEvents(),
            GameState::REVEAL => $this->revealStateEvents(),
            GameState::PREPARATION => $this->preparationStateEvents(),
        };
    }
}
