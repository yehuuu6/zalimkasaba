<?php

namespace App\Traits\ZalimKasaba;

use App\Enums\ZalimKasaba\ActionType;

trait StateEvents
{
    use ChatManager, PlayerActionsManager;

    private function dayStateEvents()
    {
        // Delete all actions
        $this->lobby->actions()->delete();
    }

    private function voteStateEvents()
    {
        if ($this->lobby->availableTrials === 3) {
            $msg = "Bu gün {$this->lobby->available_trials} oylama yapma hakkınız var.";
        } else {
            $msg = "Bu gün {$this->lobby->available_trials} oylama yapma hakkınız kaldı.";
        }
        $this->sendSystemMessage($this->lobby, $msg);
        $this->lobby->update(['available_trials' => $this->lobby->available_trials - 1]);
    }

    private function defenseStateEvents()
    {
        $accusedPlayer = $this->lobby->accused;
        $this->sendSystemMessage($this->lobby, "Sanık {$accusedPlayer->user->username}, kendini savun.");
    }

    private function judgmentStateEvents()
    {
        // Delete all the votes
        $this->lobby->votes()->delete();
    }

    private function nightStateEvents()
    {
        $offlinePlayers = $this->lobby->players()->where('is_online', false)->get();
        foreach ($offlinePlayers as $offlinePlayer) {
            //$offlinePlayer->update(['is_alive' => false]);
        }

        // Delete all the votes
        $this->lobby->votes()->delete();
        $this->lobby->update(['accused_id' => null]);
        $this->lobby->update(['available_trials' => 3]);

        $this->sendSystemMessage($this->lobby, 'Yeteneğinizi kullanabilirsiniz.');
    }

    private function revealStateEvents()
    {
        // Fetch all actions once
        $actions = $this->lobby->actions()->get();

        $killedPlayers = $this->getKilledPlayers($actions);

        if (count($killedPlayers) > 0) {
            $this->lobby->players()
                ->whereIn('id', $killedPlayers)
                ->update(['is_alive' => false]);
        }

        // Fetch all actor players with their user data once
        $actorIds = $actions->pluck('actor_id')->unique()->toArray();
        $players = $this->lobby->players()
            ->whereIn('id', $actorIds)
            ->with('user')
            ->get()
            ->keyBy('id');

        // Step 3: Process Lookout actions
        $lookoutActions = $actions->where('action_type', ActionType::WATCH);

        foreach ($lookoutActions as $action) {
            $lookoutPlayer = $players->get($action->actor_id);

            if ($lookoutPlayer) {
                $targetId = $action->target_id;

                // Get unique visitors, excluding the Lookout
                $visitors = $actions->where('target_id', $targetId)
                    ->where('actor_id', '!=', $action->actor_id)
                    ->pluck('actor_id')
                    ->unique()
                    ->toArray();

                $visitorNames = [];
                foreach ($visitors as $visitorId) {
                    $visitor = $players->get($visitorId);
                    if ($visitor) {
                        $visitorNames[] = $visitor->user->username;
                    }
                }

                // Construct and send the message
                $message = !empty($visitorNames)
                    ? "Hedefin " . implode(', ', $visitorNames) . " tarafından ziyaret edildi."
                    : "Hedefin bu gece ziyaret edilmedi.";

                $this->sendMessageToPlayer($lookoutPlayer, $message);
            }
        }
    }

    private function preparationStateEvents()
    {
        $this->assignRoles($this->lobby);
        $this->sendSystemMessage($this->lobby, 'Oyun başladı, herkesin rolü belirlendi.');
    }
}
