<?php

namespace App\Traits\ZalimKasaba;

use App\Enums\ZalimKasaba\GameState;
use App\Models\ZalimKasaba\Player;
use Masmerise\Toaster\Toaster;

trait VoteManager
{
    /**
     * Check if the given player can be voted. Used in the frontend to display the vote button.
     * @param Player $player
     * @return bool
     */
    public function canBeVoted(Player $player): bool
    {
        if ($player->id === $this->currentPlayer->id) return false;
        if ($this->lobby->state !== GameState::VOTING) return false;
        if (!$player->is_alive) return false;
        if (!$this->currentPlayer->is_alive) return false;
        return true;
    }

    /**
     * Check if the current player has voted for the given player.
     * @param Player $player
     * @return bool
     */
    public function hasVoted(Player $player): bool
    {
        // If the room has a vote for the player given by the currentPlayer, return true.
        return $this->lobby->votes()->where([
            'voter_id' => $this->currentPlayer->id,
            'target_id' => $player->id,
        ])->exists();
    }

    /**
     * Vote for the given player.
     * @param Player $targetPlayer
     */
    public function votePlayer(Player $targetPlayer)
    {
        if ($this->lobby->state !== GameState::VOTING) {
            return;
        } elseif (!$this->lobby->players()->where('id', $targetPlayer->id)->exists()) {
            Toaster::error('Oyuncu bulunamadı.');
            return;
        } elseif ($this->currentPlayer->id === $targetPlayer->id) {
            Toaster::error('Kendine oy veremezsin.');
            return;
        }

        $existingVote = $this->currentPlayer->votesGiven()->where([
            'voter_id' => $this->currentPlayer->id,
            'lobby_id' => $this->lobby->id,
        ])->first();

        if ($existingVote) {
            $existingVote->delete();
            if ($existingVote->target_id === $targetPlayer->id) {
                $this->sendSystemMessage($this->lobby, "{$this->currentPlayer->user->username}, {$targetPlayer->user->username} için verdiği oyu geri aldı.");
                return;
            }
        }

        $this->currentPlayer->votesGiven()->create([
            'lobby_id' => $this->lobby->id,
            'target_id' => $targetPlayer->id,
        ]);

        $this->sendSystemMessage($this->lobby, "{$this->currentPlayer->user->username}, {$targetPlayer->user->username} için oy kullandı.");
    }

    public function getAccusedPlayer(): ?int
    {
        $totalPlayers = $this->lobby->players()->count();
        $threshold = ceil($totalPlayers / 2);

        // Get vote counts per target player
        $voteCounts = $this->lobby->votes()
            ->selectRaw('target_id as target_player, count(*) as vote_count')
            ->groupBy('target_id')
            ->having('vote_count', '>=', $threshold)
            ->get();

        if ($voteCounts->isEmpty()) {
            return null; // No one has enough votes
        }

        // For now, assume the first player with enough votes is accused
        return $voteCounts->first()->target_player;
    }
}
