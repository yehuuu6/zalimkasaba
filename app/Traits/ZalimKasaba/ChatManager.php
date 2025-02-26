<?php

namespace App\Traits\ZalimKasaba;

use App\Enums\ZalimKasaba\ChatMessageType;
use App\Enums\ZalimKasaba\GameState;
use App\Models\ZalimKasaba\Lobby;
use App\Models\ZalimKasaba\Player;
use App\Models\ZalimKasaba\ChatMessage;
use App\Events\ZalimKasaba\NewChatMessage;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\Auth;

trait ChatManager
{
    /**
     * Sends a system message to the lobby
     * @param Lobby $lobby
     * @param string $msg
     * @return void
     */
    public function sendSystemMessage(Lobby $lobby, string $msg)
    {
        $message = ChatMessage::create([
            'lobby_id' => $lobby->id,
            'message' => $msg,
            'is_system' => true,
        ]);

        broadcast(new NewChatMessage($lobby->id, $message->id));
    }

    public function clearChat()
    {
        if (!$this->currentPlayer->is_host) {
            Toaster::error('Sadece oyun yöneticisi sohbeti silebilir.');
            return;
        }
        $this->lobby->messages()->delete();
        $this->messages = collect();
        Toaster::success('Sohbet silindi.');
    }

    public function sendMessageToPlayer(Player $player, string $msg, ChatMessageType $type = ChatMessageType::DEFAULT)
    {
        // Only send a message to the given player.
        $message = ChatMessage::create([
            'lobby_id' => $this->lobby->id,
            'message' => $msg,
            'receiver_id' => $player->user_id,
            'is_system' => true,
            'type' => $type,
        ]);

        broadcast(new NewChatMessage($this->lobby->id, $message->id));
    }

    private function canSendMessages(): bool
    {
        $statesAllowedToChat = [
            GameState::LOBBY,
            GameState::DAY,
            GameState::VOTING,
            GameState::JUDGMENT,
            GameState::PREPARATION,
        ];

        if ($this->lobby->state === GameState::DEFENSE && $this->currentPlayer->id === $this->lobby->accused_id) {
            return true;
        }

        return in_array($this->lobby->state, $statesAllowedToChat);
    }

    public function sendMessage()
    {
        if (empty($this->message) || !$this->canSendMessages()) {
            return;
        }

        $message = ChatMessage::create([
            'lobby_id' => $this->lobby->id,
            'user_id' => Auth::id(),
            'message' => $this->message,
        ]);

        $this->messages->push($message);

        // Broadcast message
        broadcast(new NewChatMessage($this->lobby->id, $message->id))->toOthers();

        $this->message = '';
    }
}
