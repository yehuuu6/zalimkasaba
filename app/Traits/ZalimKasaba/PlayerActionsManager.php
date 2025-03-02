<?php

namespace App\Traits\ZalimKasaba;

use App\Models\ZalimKasaba\Player;
use App\Enums\ZalimKasaba\GameState;
use App\Enums\ZalimKasaba\ActionType;
use App\Enums\ZalimKasaba\PlayerRole;
use Illuminate\Support\Facades\Auth;

trait PlayerActionsManager
{
    use ChatManager;

    public function selectTarget(Player $targetPlayer)
    {
        $actionType = $this->getActionType();

        $existingAction = $this->lobby->actions()->where([
            'actor_id' => $this->currentPlayer->id,
            'action_type' => $actionType,
        ])->first();

        if ($existingAction) {
            if ($existingAction->target_id === $targetPlayer->id) {
                $existingAction->delete();
                $cancelMsg = $this->getCancelActionMessage($targetPlayer);
                $this->sendMessageToPlayer($this->currentPlayer, $cancelMsg);
                return;
            }

            $existingAction->update([
                'target_id' => $targetPlayer->id,
                'action_type' => $actionType,
            ]);
        } else {
            $this->lobby->actions()->create([
                'actor_id' => $this->currentPlayer->id,
                'target_id' => $targetPlayer->id,
                'action_type' => $actionType,
            ]);
        }

        $confirmMsg = $this->getActionMessage($targetPlayer);
        $this->sendMessageToPlayer($this->currentPlayer, $confirmMsg);
    }

    /**
     * Checks if the current player can use their ability on the target player.
     * @param Player $targetPlayer The player who is targeted by the ability.
     * @return bool True if the player can use ability, false otherwise.
     */
    public function canUseAbility(Player $targetPlayer): bool
    {
        // Basic checks that must be checked for all roles
        if ($this->lobby->state !== GameState::NIGHT) return false;
        if ($this->currentPlayer->user_id !== Auth::id()) return false;

        $myRole = $this->currentPlayer->role->enum;

        if (!$this->currentPlayer->is_alive && $myRole !== PlayerRole::JESTER) return false;

        switch ($myRole) {
            case PlayerRole::GODFATHER:
                if ($this->currentPlayer->id === $targetPlayer->id) return false;
                if (in_array($targetPlayer->role->enum, PlayerRole::getMafiaRoles())) return false;
                break;
            case PlayerRole::MAFIOSO:
                if ($this->currentPlayer->id === $targetPlayer->id) return false;
                if (in_array($targetPlayer->role->enum, PlayerRole::getMafiaRoles())) return false;
                break;
            case PlayerRole::JANITOR:
                if ($this->currentPlayer->id === $targetPlayer->id) return false;
                if (in_array($targetPlayer->role->enum, PlayerRole::getMafiaRoles())) return false;
                if ($this->currentPlayer->ability_uses === 0) return false;
                break;
            case PlayerRole::DOCTOR:
                if ($targetPlayer->id === $this->currentPlayer->id && $this->currentPlayer->self_healed) return false;
                break;
            case PlayerRole::GUARD:
                if ($targetPlayer->id === $this->currentPlayer->id) return false;
                break;
            case PlayerRole::LOOKOUT:
                if ($targetPlayer->id === $this->currentPlayer->id) return false;
                break;
            case PlayerRole::HUNTER:
                if ($targetPlayer->id === $this->currentPlayer->id) return false;
                if ($this->currentPlayer->ability_uses === 0) return false;
                break;
            case PlayerRole::WITCH:
                if ($targetPlayer->id === $this->currentPlayer->id) return false;
                if ($this->currentPlayer->ability_uses === 0) return false;
                break;
            case PlayerRole::JESTER:
                if ($targetPlayer->id === $this->currentPlayer->id) return false;
                if ($this->currentPlayer->ability_uses === 0) return false;
                break;
            case PlayerRole::ANGEL:
                if ($targetPlayer->id !== $this->currentPlayer->id) return false;
                if ($this->currentPlayer->ability_uses === 0) return false;
                break;
            default:
                return false;
        }
        return true;
    }

    /**
     * Kills the target player and sets their death night.
     * @param Player $player The player who is targeted by the ability.
     * @param bool $canHaunt Whether the player can haunt after death. (Jester)
     * @return void
     */
    private function killPlayer(Player $player, bool $canHaunt = false): void
    {
        $player->update([
            'is_alive' => false,
            'death_night' => $this->lobby->day_count,
            'can_haunt' => $canHaunt,
        ]);
    }

    /**
     * Returns the action name of the player role for the button in the frontend
     * @return string
     */
    public function getAbilityName(): string
    {
        return match ($this->currentPlayer->role->enum) {
            PlayerRole::GODFATHER => 'Öldür',
            PlayerRole::MAFIOSO => 'Öldür',
            PlayerRole::JANITOR => 'Temizle',
            PlayerRole::DOCTOR => 'Koru',
            PlayerRole::GUARD => 'Sorgula',
            PlayerRole::LOOKOUT => 'Dikizle',
            PlayerRole::HUNTER => 'Vur',
            PlayerRole::WITCH => 'Zehirle',
            PlayerRole::JESTER => 'Lanetle',
            PlayerRole::ANGEL => 'Koru',
        };
    }

    /**
     * Send target player a message about the action they have taken.
     * @param Player $targetPlayer The player who is targeted by the action.
     * @return string The message to be sent to the player.
     */
    private function getActionMessage(Player $targetPlayer): string
    {
        $name = $targetPlayer->user->username;

        return match ($this->currentPlayer->role->enum) {
            PlayerRole::GODFATHER => "{$name} adlı oyuncunun öldürülmesi için emir verdin.",
            PlayerRole::MAFIOSO => "{$name} adlı oyuncuyu öldürme kararı aldın.",
            PlayerRole::JANITOR => "{$name} adlı oyuncunun kimliğini temizlemeye karar verdin.",
            PlayerRole::DOCTOR => "{$name} adlı oyuncuyu koruma kararı aldın.",
            PlayerRole::GUARD => "{$name} adlı oyuncuya GBT sorgusu yapmaya karar verdin.",
            PlayerRole::LOOKOUT => "{$name} adlı oyuncunun evini dikizlemeye karar verdin.",
            PlayerRole::HUNTER => "{$name} adlı oyuncuyu vurmaya karar verdin.",
            PlayerRole::WITCH => "{$name} adlı oyuncuyu zehirlemeye karar verdin.",
            PlayerRole::JESTER => "{$name} adlı oyuncuyu lanetlemeye karar verdin.",
            PlayerRole::ANGEL => "Güzelliğini açığa çıkarmaya karar verdin."
        };
    }

    private function sendNightAbilityMessages()
    {
        $players = $this->lobby->players()->get();

        foreach ($players as $player) {
            $availableUses = $player->ability_uses;
            if ($availableUses === null) {
                $msg = "Yeteneğinizi kullanabilirsiniz.";
            } else {
                if ($availableUses > 0) {
                    $msg = match ($player->role->enum) {
                        PlayerRole::HUNTER => "{$availableUses} adet mermin var. Birini vurabilirsin.",
                        PlayerRole::WITCH => "{$availableUses} adet zehirin var. Birini zehirleyebilirsin.",
                        PlayerRole::ANGEL => "{$availableUses} defa güzelliğini kullanarak kendini koruyabilirsin."
                    };
                } elseif ($availableUses === 0) {
                    $msg = match ($player->role->enum) {
                        PlayerRole::HUNTER => "Bütün mermilerini kullandın. Artık dinlenebilirsin.",
                        PlayerRole::WITCH => "Zehirlerinin tümünü kullandın. Beklemekten başka çaren yok.",
                        PlayerRole::ANGEL => "Güzelliğini kullanma hakkın bitti. Tamamen savunmasız durumdasın."
                    };
                }
            }
            $this->sendMessageToPlayer($player, $msg);
        }
    }

    private function getCancelActionMessage(Player $targetPlayer): string
    {
        $name = $targetPlayer->user->username;

        return match ($this->currentPlayer->role->enum) {
            PlayerRole::GODFATHER => "{$name} adlı oyuncuyu öldürme emrini iptal ettin.",
            PlayerRole::MAFIOSO => "{$name} adlı oyuncuyu öldürmemeye karar verdin.",
            PlayerRole::JANITOR => "{$name} adlı oyuncunun kimliğini temizlemekten vaz geçtin.",
            PlayerRole::DOCTOR => "{$name} adlı oyuncuyu korumaktan vaz geçtin.",
            PlayerRole::GUARD => "{$name} adlı oyuncuya GBT sorgusu yapmaktan vaz geçtin.",
            PlayerRole::LOOKOUT => "{$name} adlı oyuncunun evini dikizlemekten vaz geçtin.",
            PlayerRole::HUNTER => "{$name} adlı oyuncuyu vurmaktan vaz geçtin.",
            PlayerRole::WITCH => "{$name} adlı oyuncuyu zehirlemekten vaz geçtin.",
            PlayerRole::JESTER => "{$name} adlı oyuncuyu lanetlemekten vaz geçtin.",
            PlayerRole::ANGEL => "Güzelliğini açığa çıkarmaktan vaz geçtin."
        };
    }

    /**
     * Gets the action type that the current player can perform based on their role.
     * @return ActionType The action type that the player can perform.
     */
    private function getActionType(): ActionType
    {
        return match ($this->currentPlayer->role->enum) {
            PlayerRole::GODFATHER => ActionType::ORDER,
            PlayerRole::MAFIOSO => ActionType::KILL,
            PlayerRole::JANITOR => ActionType::CLEAN,
            PlayerRole::DOCTOR => ActionType::HEAL,
            PlayerRole::GUARD => ActionType::INTERROGATE,
            PlayerRole::LOOKOUT => ActionType::WATCH,
            PlayerRole::HUNTER => ActionType::SHOOT,
            PlayerRole::ANGEL => ActionType::REVEAL,
            PlayerRole::WITCH => ActionType::POISON,
            PlayerRole::JESTER => ActionType::HAUNT,
            PlayerRole::ANGEL => ActionType::REVEAL,
        };
    }

    /**
     * Checks if the current player has used their ability on the target player.
     * @param Player $targetPlayer The player who is targeted by the ability.
     * @return bool True if the player has used ability, false otherwise.
     */
    public function hasUsedAbility(Player $targetPlayer): bool
    {
        return $this->lobby->actions()->where([
            'actor_id' => $this->currentPlayer->id,
            'target_id' => $targetPlayer->id,
        ])->exists();
    }
}
