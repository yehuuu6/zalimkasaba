<div x-data="{
    sendMessage() {
            $wire.sendMessage();
            this.showLatestMessage(true);
        },
        showLatestMessage(overrideCondition = false) {
            let isViewingLatestMessage = this.$refs.gameChat.scrollHeight - this.$refs.gameChat.scrollTop === this.$refs.gameChat.clientHeight;
            if (isViewingLatestMessage || overrideCondition) {
                setTimeout(() => {
                    this.$refs.gameChat.scrollTop = this.$refs.gameChat.scrollHeight;
                }, 100);
            }
        },
}" class="bg-white rounded flex-grow shadow-sm p-6 flex flex-col">
    <div x-init="showLatestMessage(true)" x-ref="gameChat" x-on:chat-message-received.window="showLatestMessage()"
        class="overflow-y-auto flex-grow h-60 bg-gray-50 rounded flex flex-col gap-2 p-2">
        @forelse ($messages as $msg)
            @if ($msg->receiver_id && $msg->receiver_id !== Auth::id())
                @continue
            @endif
            <div wire:key="msg-{{ $msg->id }}">
                <p>
                    <span class="text-gray-700 font-semibold">
                        {{ $msg->created_at->format('H:i') }}
                    </span>
                    @if ($msg->receiver_id)
                        <span
                            :class="{
                                'text-gray-800': '{{ $msg->type }}'
                                === 'default',
                                'bg-red-500 text-white px-1 rounded': '{{ $msg->type }}'
                                === 'warning',
                                'bg-green-500 text-white px-1 rounded': '{{ $msg->type }}'
                                === 'success'
                            }"
                            class="font-bold">
                            {{ $msg->message }}
                        </span>
                    @else
                        <span class="text-gray-800 font-bold">
                            @if ($msg->is_system)
                                <span class="text-blue-500">
                                    SİSTEM:
                                </span>
                            @else
                                <span class="text-amber-500">
                                    {{ $msg->user->username }}:
                                </span>
                            @endif
                        </span>
                        <span class="text-gray-600 break-all">
                            {{ $msg->message }}
                        </span>
                    @endif
                </p>
            </div>
        @empty
            <div class="flex gap-1">
                <span class="text-gray-700 font-semibold">
                    {{ now()->format('H:i') }}
                </span>
                <span class="font-bold text-blue-500">
                    SİSTEM:
                </span>
                <span class="text-gray-600">
                    Oyun hazır, başlamak için oyuncuları bekleyin.
                </span>
            </div>
        @endforelse
    </div>
    <div class="flex items-center gap-1 mt-3">
        <input x-on:keydown.enter="sendMessage()" type="text" wire:model="message"
            class="flex-grow px-4 py-2 text-sm border border-gray-300 rounded-full" placeholder="Mesajınızı yazın...">
        <button type="button" x-on:click="sendMessage()"
            class="duration-300 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-emerald-400 hover:to-lime-600 rounded-full p-2 text-white transition-all transform hover:scale-105">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m22 2-7 20-4-9-9-4Z" />
                <path d="M22 2 11 13" />
            </svg>
        </button>
        @if ($currentPlayer->is_host)
            <button type="button" wire:click="clearChat"
                class="duration-300 bg-opacity-50 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-emerald-400 hover:to-lime-600 rounded-full p-2 text-white transition-all transform hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18" />
                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                    <line x1="10" x2="10" y1="11" y2="17" />
                    <line x1="14" x2="14" y1="11" y2="17" />
                </svg>
            </button>
        @endif
    </div>
</div>
