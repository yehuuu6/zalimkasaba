<?php

namespace App\Traits\ZalimKasaba;

use App\Enums\ZalimKasaba\LobbyStatus;
use App\Models\ZalimKasaba\Player;

trait PlayerManager
{
    private function getPlayerById(int $userId): Player | null
    {
        return $this->lobby->players()->where('user_id', $userId)->first();
    }

    public function handleUsersHere($users)
    {
        $offlinePlayerIds = $this->lobby->players()
            ->whereNotIn('user_id', collect($users)->pluck('id'))
            ->pluck('id');

        if ($offlinePlayerIds->isNotEmpty()) {
            $this->lobby->players()->whereIn('id', $offlinePlayerIds)->update([
                'is_online' => false,
            ]);
        }

        // Grab the host player
        $hostId = collect($users)->first(fn($user) => $user['id'] === $this->lobby->host_id);

        if (!$hostId) {
            $this->lobby->update(['status' => 'waiting_host']);
            return redirect()->route('lobbies')->warning('Oyun yöneticisi aktif değil.');
        } else {
            $this->lobby->update(['status' => 'active']);
        }
    }

    public function handleUserJoined($user)
    {
        $player = $this->getPlayerById($user['id']);

        if ($player->is_host) {
            $this->lobby->update(['status' => 'active']);
            $this->dispatch('host-returned');
        }

        if ($player && !$player->is_online) {
            $player->update([
                'is_online' => true,
                'last_seen' => now(),
            ]);
        }
    }

    public function handleUserLeft($user)
    {
        $player = $this->getPlayerById($user['id']);

        if ($player && $player->is_online) {
            $player->update([
                'is_online' => false,
            ]);

            if ($player->is_host && $this->lobby->status !== LobbyStatus::WAITING_HOST) {
                $this->lobby->update(['status' => 'waiting_host']);
                $this->dispatch('host-left');
            }
        }
    }
}
