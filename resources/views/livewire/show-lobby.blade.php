<div class="h-[calc(100vh-16rem)]" x-data="{
    waitHostInterval: null,
    closeTimerTimeout: null,
    hostReturned() {
        clearInterval(this.waitHostInterval);
        clearTimeout(this.closeTimerTimeout);
        Toaster.success('Yönetici geri döndü.');
    },
    waitForHost() {
        let count = 30;
        Toaster.warning(`Yönetici oyundan ayrıldı. Bekleniyor... ${count}`);
        this.waitHostInterval = setInterval(() => {
            count = count - 5;
            Toaster.info(`Yönetici bekleniyor ${count}...`);
        }, 5000);
        this.closeTimerTimeout = setTimeout(() => {
            clearInterval(this.waitHostInterval);
            Toaster.error('Yönetici gelmedi. Oda kapatılıyor.');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }, 30000);
    },
}" x-on:host-left.window="waitForHost()"
    x-on:host-returned.window="hostReturned()">
    <div class="flex gap-5 h-full">
        <div class="hidden lg:flex flex-col gap-5 w-52 lg:w-72 h-full flex-shrink-0">
            @if ($lobby->state !== App\Enums\ZalimKasaba\GameState::LOBBY)
                <div class="flex-grow-0 bg-white rounded shadow-sm p-6">
                    <h1 class="text-2xl text-gray-800 font-semibold">
                        Mezarlık
                    </h1>
                    <ul class="mt-2 flex flex-col gap-2">
                        @forelse ($lobby->players()->where('is_alive', false)->get() as $deadPlayer)
                            <li class="flex text-sm items-center justify-between w-full gap-2 rounded"
                                wire:key="dead-player-{{ $deadPlayer->id }}">
                                <span class="text-gray-600 font-medium"
                                    :class="{ 'line-through': {{ !$deadPlayer->is_online }} }">
                                    🪦 {{ $deadPlayer->user->username }}
                                </span>
                                <span class="text-gray-500 text-xs capitalize">
                                    {{ $deadPlayer->role->name }}
                                </span>
                            </li>
                        @empty
                            <li class="flex items-center gap-2 p-2 rounded">
                                <span class="text-gray-500 text-sm">
                                    Mezarlık boş.
                                </span>
                            </li>
                        @endforelse
                    </ul>
                </div>
            @endif
            <div class="flex flex-col gap-5 flex-grow flex-shrink-0">
                <div class="bg-white rounded shadow-sm flex flex-col p-1 h-full">
                    <h1 class="text-2xl text-gray-800 font-semibold p-5 pb-0">Roller</h1>
                    <ul class="flex flex-col gap-2 mt-1.5 flex-grow overflow-y-auto h-0 px-5 pb-5 pt-0">
                        @forelse ($lobby->roles as $role)
                            <li class="flex items-center p-2 gap-2 rounded" wire:key="lobby-role-{{ $role->id }}">
                                {{ $role->icon }}
                                <span class="text-gray-800 font-semibold">
                                    {{ $role->name }}
                                </span>
                            </li>
                        @empty
                            <li class="flex items-center gap-2 p-2 rounded">
                                <span class="text-gray-800 font-semibold">
                                    Odada belirlenen roller yok.
                                </span>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <div class="flex flex-col flex-grow gap-5 h-full">
            <div class="flex bg-white rounded shadow-sm items-center justify-between p-6">
                <span class="text-gray-800 font-bold text-2xl">
                    {{ $gameTitle }}
                </span>
                <livewire:show-game-timer :$lobby />
            </div>
            <livewire:chat-window :$lobby :$currentPlayer />
        </div>
        <div class="flex flex-col gap-5 w-72 h-full flex-shrink-0">
            @if ($this->lobby->state !== App\Enums\ZalimKasaba\GameState::LOBBY && $this->currentPlayer->role)
                <div x-data="{
                    isExpanded: true,
                    toggle() {
                        this.isExpanded = !this.isExpanded;
                    }
                }" class="overflow-y-auto bg-white rounded shadow-sm p-6">
                    <h1 class="text-lg flex items-center justify-between gap-2 text-gray-800 font-semibold text-center">
                        {{ $this->currentPlayer->role->icon }}
                        <span class="font-bold">
                            {{ $this->currentPlayer->role->name }}
                        </span>
                        <button type="button" x-on:click="toggle()" x-text="isExpanded ? '—' : '+'"
                            class="bg-gray-200 size-7 hover:bg-gray-100 text-gray-700 font-semibold px-2 py-1 text-xs rounded">
                            —
                        </button>
                    </h1>
                    <div x-show="isExpanded" class="mt-5">
                        <x-seperator />
                        <p class="mt-2">
                            <span class="font-medium text-gray-700 text-sm">Grup:</span>
                            <span class="text-sm"
                                :class="{
                                    'text-red-600': '{{ $this->currentPlayer->role->enum->getFaction() }}' == 'Mafya',
                                    'text-green-600': '{{ $this->currentPlayer->role->enum->getFaction() }}' ==
                                        'Kasaba',
                                }">
                                {{ $this->currentPlayer->role->enum->getFaction() }}
                            </span>
                        </p>
                        <div class="mt-1">
                            <h4 class="font-medium text-gray-700 text-sm">
                                Amaç:
                            </h4>
                            <span class="text-gray-500 text-xs">
                                {{ $this->currentPlayer->role->enum->getGoal() }}
                            </span>
                        </div>
                        <div class="mt-1">
                            <h4 class="font-medium text-gray-700 text-sm">Yetenek:</h4>
                            <span class="text-gray-500 text-xs">
                                {{ $this->currentPlayer->role->enum->getDescription() }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif
            <div class="flex rounded shadow-sm flex-col flex-grow flex-shrink-0 bg-white p-6">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl text-gray-800 font-semibold">Oyuncular</h1>
                    <span class="text-gray-500 text-sm font-medium">
                        {{ $lobby->players->count() }} / {{ $lobby->max_players }}
                    </span>
                </div>
                <ul class="flex flex-col gap-2 mt-1.5 flex-grow overflow-y-auto h-0">
                    @forelse ($lobby->players()->orderBy('place')->where('is_alive', true)->get() as $player)
                        <li wire:key="player-{{ $player->id }}"
                            class="flex items-center justify-between gap-4 rounded-lg transition-colors">
                            <div class="flex items-center gap-2">
                                <span
                                    :class="{
                                        'bg-green-500': {{ $player->is_online }},
                                        'bg-red-500': !
                                            {{ $player->is_online }}
                                    }"
                                    class="flex items-center justify-center size-5 rounded-full text-white text-xs font-semibold">
                                    {{ $player->place }}
                                </span>
                                <span class="font-medium text-sm"
                                    :class="{
                                        'text-blue-700': {{ $player->id }} === {{ $currentPlayer->id }},
                                    }">
                                    {{ $player->user->username }}
                                </span>
                                @if ($player->is_host)
                                    👑
                                @endif
                                @if ($lobby->state === App\Enums\ZalimKasaba\GameState::VOTING)
                                    <span class="text-gray-500 text-xs">
                                        ({{ $player->votesReceived->count() }})
                                    </span>
                                @endif
                            </div>
                            @if (
                                $lobby->state === App\Enums\ZalimKasaba\GameState::LOBBY &&
                                    $currentPlayer->is_host &&
                                    $player->id !== $currentPlayer->id)
                                <button type="button" wire:click="kickPlayer({{ $player->id }})"
                                    class="bg-red-500 hover:bg-red-600 text-white font-semibold px-2 py-1 text-xs rounded">
                                    KOV
                                </button>
                            @elseif ($this->canBeVoted($player))
                                <button type="button" wire:click="votePlayer({{ $player->id }})"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-2 py-1 text-xs rounded">
                                    @if ($this->hasVoted($player))
                                        İPTAL
                                    @else
                                        OY VER
                                    @endif
                                </button>
                            @elseif (
                                $lobby->state === App\Enums\ZalimKasaba\GameState::NIGHT &&
                                    $this->currentPlayer->is_alive &&
                                    $this->getPlayerActionName($currentPlayer) !== null &&
                                    $currentPlayer->user_id === Auth::id() &&
                                    $currentPlayer->id !== $player->id)
                                <button type="button" wire:click="performPlayerAction({{ $player->id }})"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-2 py-1 text-xs rounded">
                                    @if ($this->hasPerformedAction($player))
                                        İPTAL
                                    @else
                                        {{ $this->getPlayerActionName($currentPlayer) }}
                                    @endif
                                </button>
                            @endif
                        </li>
                    @empty
                        <li class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded">
                            <span class="text-gray-800 font-semibold">Henüz kimse katılmadı.</span>
                        </li>
                    @endforelse
                </ul>
                @if ($currentPlayer->is_host && $lobby->state === App\Enums\ZalimKasaba\GameState::LOBBY)
                    <button wire:click="startGame" type="button"
                        class="bg-blue-500 hover:bg-blue-600 text-white rounded p-2 font-bold text-xl">
                        Oyunu Başlat
                    </button>
                @endif
            </div>
        </div>
    </div>

</div>
