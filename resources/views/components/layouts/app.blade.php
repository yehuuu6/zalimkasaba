<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>{{ $title ?? 'Page Title' }}</title>
</head>

<body class="flex flex-col min-h-screen w-full bg-slate-100 font-inter antialiased">
    <nav
        class="flex py-8 px-[12%] bg-white border-b border-gray-200 bg-opacity-80 sticky top-0 items-center justify-between">
        <a href="{{ route('lobbies') }}" class="text-4xl font-extrabold text-gray-800">
            Zalim Kasaba
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('lobbies') }}" class="text-lg font-semibold text-gray-800 hover:underline">Odalar
            </a>
            @guest
                <a href="{{ route('login') }}" class="text-lg font-semibold text-gray-800 hover:underline">Giriş
                </a>
                <a href="{{ route('register') }}" class="text-lg font-semibold text-gray-800 hover:underline">Kayıt Ol
                </a>
            @endguest
            @auth
                <span class="text-lg font-semibold text-gray-800">
                    {{ Auth::user()->name }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-lg font-semibold text-gray-800 hover:underline">Çıkış
                    </button>
                </form>
            @endauth
        </div>
    </nav>
    <main class="mx-[3%] mt-4 md:mx-[6%] md:mt-8 mb-7 md:mb-14 lg:mx-[12%]">
        {{ $slot }}
    </main>
    <footer class="py-12 px-[3%] md:px-[6%] lg:px-[12%] bg-blue-950 mt-auto">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex gap-2 flex-col text-gray-200">
                <h6 class="text-white font-semibold mb-1 text-lg">Gazi Social</h6>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('lobbies') }}" class="font-normal text-sm self-start">
                        Ana Sayfa
                    </a>
                    <a href="{{ route('lobbies') }}" class="font-normal text-sm self-start">
                        Hakkımızda
                    </a>
                    <a href="{{ route('lobbies') }}" class="font-normal text-sm self-start">
                        Kayıt Ol
                    </a>
                </div>
            </div>
            <div class="flex gap-2 flex-col text-gray-200">
                <h6 class="text-white font-semibold mb-1 text-lg">İletişim</h6>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('lobbies') }}" class="font-normal text-sm self-start">
                        Mesaj Gönder
                    </a>
                    <a href="{{ route('lobbies') }}" class="font-normal text-sm self-start">
                        Hata Bildir
                    </a>
                    <a href="{{ route('lobbies') }}" class="font-normal text-sm self-start">
                        Bildirilen Hatalar
                    </a>
                </div>
            </div>
            <div class="flex gap-2 flex-col text-gray-200">
                <h6 class="text-white font-semibold mb-1 text-lg">Yardım</h6>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('lobbies') }}" class="font-normal text-sm self-start">
                        SSS
                    </a>
                    <a href="{{ route('lobbies') }}" class="font-normal text-sm self-start">
                        Gizlilik Politikası
                    </a>
                    <a href="{{ route('lobbies') }}" class="font-normal text-sm self-start">
                        Kullanım Koşulları
                    </a>
                </div>
            </div>
            <div class="flex gap-2 flex-col text-gray-200">
                <h6 class="text-white font-semibold mb-1 text-lg">Dev Center</h6>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('lobbies') }}" class="font-normal text-sm self-start">
                        Introduction
                    </a>
                    <a href="{{ route('lobbies') }}" class="font-normal text-sm self-start">
                        Contribution Guide
                    </a>
                    <a href="{{ route('lobbies') }}" class="font-normal text-sm self-start">
                        Contributors
                    </a>
                </div>
            </div>
        </div>
        <div class="my-8 opacity-30">
            <x-seperator />
        </div>
        <div class="w-full text-gray-300 mb-1.5 text-sm md:text-center">
            <span>Made with 🧠 by contributors and </span>
            <a href="https://github.com/yehuuu6" target="_blank"
                class="text-blue-200 font-normal hover:underline">@yehuuu6</a>
        </div>
        <div class="w-full text-gray-300 md:text-center text-sm">
            <a href="https://www.gnu.org/licenses/gpl-3.0.en.html" target="_blank" class="hover:underline">GPLv3</a>
            <span> | </span>
            <a href="https://github.com/yehuuu6/gazisocial" target="_blank" class="hover:underline">GitHub</a>
            <span> | </span>
            <span>Copyright © Gazi Social <span x-data="{ date: '2025' }" x-init="date = new Date().getFullYear()" x-text="date"></span>
                All rights
                reserved.</span>
        </div>
    </footer>
    <x-toaster-hub />
    @livewireScriptConfig
</body>

</html>
