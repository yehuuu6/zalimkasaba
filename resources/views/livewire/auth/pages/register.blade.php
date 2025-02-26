<div class="flex items-center flex-col pt-16 p-6">
    <a href="/" class="text-6xl mb-6 hover:opacity-90">
        Zalim Kasaba
    </a>
    <div class="w-full max-w-md">
        <form wire:submit="register" class="bg-slate-50 border border-slate-200 px-8 pt-6 pb-8 mb-4 rounded-lg">
            @csrf
            <div class="mb-4 flex gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-normal mb-2" for="name">
                        Ad Soyad
                    </label>
                    <input maxlength="30"
                        class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="name" type="text" name="name" value="{{ old('name') }}" required
                        wire:model="name">
                </div>
                <div>
                    <label for="username" class="block text-gray-700 text-sm font-normal mb-2">
                        Kullanıcı Adı
                    </label>
                    <input maxlength="30"
                        class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="username" type="text" name="username" value="{{ old('username') }}" required
                        wire:model="username">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-normal mb-2" for="email">
                    E-posta
                </label>
                <input placeholder="ornek@gazi.edu.tr" maxlength="255"
                    class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="email" type="email" name="email" value="{{ old('email') }}" wire:model="email"
                    autocomplete="mail" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-normal mb-2" for="gender">
                    Cinsiyet
                </label>
                <select
                    class="border rounded bg-white w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="gender" wire:model="gender" required>
                    <option value="belirtilmemiş" selected>
                        Belirtilmemiş
                    </option>
                    <option value="erkek">Erkek</option>
                    <option value="kadın">Kadın</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-normal mb-2" for="password">
                    Şifre
                </label>
                <input minlength="8"
                    class="appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
                    id="password" type="password" name="password" required wire:model="password">
            </div>
            <div class="mb-1">
                <label class="block text-gray-700 text-sm font-normal mb-2" for="password">
                    Şifreyi Onayla
                </label>
                <input minlength="8"
                    class="appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
                    id="password_confirmation" type="password" name="password_confirmation" required
                    wire:model="password_confirmation">
            </div>

            <div class="mb-4 flex items-center gap-1">
                <input id="accept_terms" type="checkbox" value="0" wire:model="accept_terms"
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                <label for="accept_terms" class="ms-2 text-sm font-light text-gray-600">
                    <a href="/" target="_blank" class="text-blue-600 hover:underline font-normal">
                        Kullanıcı Sözleşmesi</a>'ni okudum ve kabul ediyorum.
                </label>
            </div>


            <div class="flex items-center justify-between flex-col gap-3">
                <button wire:loading.class="bg-gray-200 cursor-not-allowed"
                    wire:loading.class.remove = "bg-blue-600 hover:bg-opacity-100" wire:loading.attr="disabled"
                    class="bg-blue-600 flex items-center h-[40px] gap-1 overflow-hidden justify-center bg-opacity-90 hover:bg-opacity-100 text-white font-medium py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full"
                    type="submit">
                    <span wire:loading.remove>Kayıt Ol</span>
                </button>
            </div>
        </form>
    </div>
    <div class="text-center">
        <p class="text-gray-600 text-sm">Zaten bir hesabınız var mı?
            <a href="/login"
                class="inline-block align-baseline font-normal text-sm text-blue-600 hover:underline">Giriş
                Yapın</a>
        </p>
    </div>
</div>
