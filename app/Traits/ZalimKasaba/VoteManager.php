<?php

namespace App\Traits\ZalimKasaba;

use App\Enums\ZalimKasaba\FinalVoteType;
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

        // If there is a tie, return null
        if ($voteCounts->count() > 1) {
            return null;
        } else {
            return $voteCounts->first()->target_player;
        }
    }

    private function buildFinalVoteArray(FinalVoteType $type, int $innoCount, int $guiltyCount, int $abstainCount): array
    {
        return [
            'type' => $type->value,
            'inno' => $innoCount,
            'guilty' => $guiltyCount,
            'abstain' => $abstainCount,
        ];
    }

    private function calculateFinalVotes(): array
    {
        $accusedPlayer = $this->lobby->accused;

        $innocentVotes = $this->lobby->finalVotes()->where([
            'target_id' => $accusedPlayer->id,
            'type' => FinalVoteType::INNOCENT,
        ])->count();

        $guiltyVotes = $this->lobby->finalVotes()->where([
            'target_id' => $accusedPlayer->id,
            'type' => FinalVoteType::GUILTY,
        ])->count();

        // Calculate abstain votes
        $totalPlayers = $this->lobby->players()->count() - 1; // Exclude the accused player
        $abstainVotes = $totalPlayers - $innocentVotes - $guiltyVotes;

        if ($guiltyVotes > $innocentVotes) {
            return $this->buildFinalVoteArray(FinalVoteType::GUILTY, $innocentVotes, $guiltyVotes, $abstainVotes);
        } else {
            return $this->buildFinalVoteArray(FinalVoteType::INNOCENT, $innocentVotes, $guiltyVotes, $abstainVotes);
        }
    }

    public function finalVote(string $type): void
    {
        $type = FinalVoteType::tryFrom($type);
        $accusedPlayer = $this->lobby->accused;
        if (!$accusedPlayer || $this->lobby->state !== GameState::JUDGMENT) return;

        $existingVote = $this->lobby->finalVotes()->where([
            'voter_id' => $this->currentPlayer->id,
            'target_id' => $accusedPlayer->id,
        ])->first();

        if ($existingVote) {
            if ($existingVote->type === $type) {
                $existingVote->delete();
                $this->sendSystemMessage($this->lobby, "{$this->currentPlayer->user->username} verdiği oyu geri aldı.");
                return;
            } else {
                $existingVote->update(['type' => $type]);
                $this->sendSystemMessage($this->lobby, "{$this->currentPlayer->user->username} oyunu değiştirdi.");
                return;
            }
        } else {
            $this->lobby->finalVotes()->create([
                'voter_id' => $this->currentPlayer->id,
                'target_id' => $accusedPlayer->id,
                'type' => $type,
            ]);
            $this->sendSystemMessage($this->lobby, "{$this->currentPlayer->user->username} oyunu kullandı.");
        }
    }

    private function hasVotedInno(): bool
    {
        return $this->lobby->finalVotes()->where([
            'voter_id' => $this->currentPlayer->id,
            'type' => FinalVoteType::INNOCENT,
        ])->exists();
    }

    private function hasVotedGuilty(): bool
    {
        return $this->lobby->finalVotes()->where([
            'voter_id' => $this->currentPlayer->id,
            'type' => FinalVoteType::GUILTY,
        ])->exists();
    }

    private function setJudgeModalState()
    {
        if ($this->lobby->state === GameState::JUDGMENT && $this->currentPlayer->id !== $this->lobby->accused_id) {
            $this->judgeModal = true;
        } else {
            $this->judgeModal = false;
        }
    }
}
