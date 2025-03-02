<?php

namespace App\Traits\ZalimKasaba;

trait StateEnterEvents
{
    use ChatManager, PlayerActionsManager;

    private function enterDay()
    {
        // Delete all actions
        $this->lobby->actions()->delete();
    }

    private function enterVoting()
    {
        $this->lobby->finalVotes()->delete();
        $totalPlayers = $this->lobby->players()->count();
        $threshold = ceil($totalPlayers / 2);
        $trialCount = $this->lobby->available_trials;
        $this->sendSystemMessage($this->lobby, "Bu gün {$trialCount} oylama yapma hakkınız kaldı. Sorgulama yapmak için {$threshold} oy gerekiyor.");
        $this->lobby->update(['available_trials' => $trialCount - 1]);
    }

    private function enterDefense()
    {
        $accusedPlayer = $this->lobby->accused;
        $this->sendSystemMessage($this->lobby, "Sanık {$accusedPlayer->user->username}, kendini savun.");
    }

    private function enterJudgment()
    {
        // Delete all the votes
        $this->lobby->votes()->delete();
    }

    private function enterLastWords()
    {
        $accusedPlayer = $this->lobby->accused;
        $this->sendSystemMessage($this->lobby, "Sanık {$accusedPlayer->user->username}, son sözlerini söyle.");
    }

    private function enterNight()
    {
        $offlinePlayers = $this->lobby->players()->where('is_online', false)->get();
        foreach ($offlinePlayers as $offlinePlayer) {
            //$this->killPlayer($offlinePlayer);
        }

        // Delete all the votes
        $this->lobby->finalVotes()->delete();
        $this->lobby->votes()->delete();
        $this->lobby->update(['available_trials' => 3]);

        $this->sendNightAbilityMessages();
    }

    private function enterReveal()
    {
        $actions = $this->lobby->actions()->get();
    }

    private function enterPreparation()
    {
        $this->assignRoles($this->lobby);
        $this->sendSystemMessage($this->lobby, 'Oyun başladı, herkesin rolü belirlendi.');
    }
}
