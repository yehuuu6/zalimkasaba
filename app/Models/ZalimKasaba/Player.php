<?php

namespace App\Models\ZalimKasaba;

use App\Models\User;
use App\Enums\ZalimKasaba\PlayerRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Player extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'last_seen' => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(GameRole::class, 'game_role_id');
    }

    public function votesGiven()
    {
        return $this->hasMany(LynchVote::class, 'voter_id');
    }

    public function votesReceived()
    {
        return $this->hasMany(LynchVote::class, 'target_id');
    }

    public function lobby(): BelongsTo
    {
        return $this->belongsTo(Lobby::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
