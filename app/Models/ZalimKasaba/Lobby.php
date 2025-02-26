<?php

namespace App\Models\ZalimKasaba;

use App\Models\User;
use App\Enums\ZalimKasaba\GameState;
use App\Enums\ZalimKasaba\LobbyStatus;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lobby extends Model
{
    /** @use HasFactory<\Database\Factories\LobbyFactory> */
    use HasFactory;

    protected $guarded = ['id', 'uuid'];

    // Create an uuid on creating a new lobby
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lobby) {
            $lobby->uuid = Str::uuid();
        });
    }

    protected $casts = [
        'status' => LobbyStatus::class,
        'state' => GameState::class,
        'countdown_start' => 'datetime',
        'countdown_end' => 'datetime',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(GameRole::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(LynchVote::class);
    }

    public function accused(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'accused_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(GameAction::class);
    }
}
