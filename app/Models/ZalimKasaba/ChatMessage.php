<?php

namespace App\Models\ZalimKasaba;

use App\Enums\ZalimKasaba\ChatMessageType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'type' => ChatMessageType::class,
    ];

    public function lobby(): BelongsTo
    {
        return $this->belongsTo(Lobby::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
