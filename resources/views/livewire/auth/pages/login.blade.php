<div class="flex items-center flex-col pt-16 p-6">
    <a href="/" class="text-6xl mb-6 hover:opacity-90">
        Zalim Kasaba
    </a>
    <form wire:submit="login" class="bg-slate-50 border border-slate-200 px-8 pt-6 pb-8 mb-4 rounded-lg max-w-md w-full">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-normal mb-2" for="email">
                E-posta
            </label>
            <input maxlength="255"
                class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                id="email" type="email" name="email" value="{{ old('email') }}" required wire:model="email"
                autocomplete="mail">
        </div>
        <div>
            <label class="block text-gray-700 text-sm font-normal mb-2" for="password">
                Şifre
            </label>
            <input
                class="appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
                id="password" type="password" name="password" required wire:model="password">
        </div>
        <div class="mb-4 flex items-center gap-1">
            <input id="remember" type="checkbox" value="0" wire:model="remember"
                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
            <label for="remember" class="ms-2 text-sm font-normal text-gray-600">
                Beni hatırla
            </label>
        </div>
        <div class="flex items-center justify-between flex-col gap-3">
            <button wire:loading.class="bg-gray-200 cursor-not-allowed"
                wire:loading.class.remove = "bg-blue-600 hover:bg-opacity-100" wire:loading.attr="disabled"
                class="bg-blue-600 flex items-center h-[40px] gap-1 overflow-hidden justify-center bg-opacity-90 hover:bg-opacity-100 text-white font-medium py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full"
                type="submit">
                <span wire:loading.remove>Giriş Yap</span>
            </button>
        </div>
    </form>
    <div class="text-center">
        <p class="text-gray-600 text-sm">Hesabınız yok mu?
            <a href="/register" class="inline-block align-baseline font-normal text-sm text-blue-600 hover:underline">
                Kayıt Olun
            </a>
        </p>
    </div>
</div>
