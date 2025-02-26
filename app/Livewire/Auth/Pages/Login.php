<?php

namespace App\Livewire\Auth\Pages;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Redirect;

#[Title('Giriş Yap')]
class Login extends Component
{

    public $email;
    public $password;
    public $remember;

    public function logout()
    {
        Auth::logout();

        return redirect(route('login'))->info('Hesabınızdan çıkış yaptınız.');
    }

    public function login()
    {

        $messages = [
            'email.required' => 'Email alanı boş bırakılamaz.',
            'email.email' => 'Geçerli bir email adresi giriniz.',
            'password.required' => 'Şifre alanı boş bırakılamaz.',
        ];

        try {
            $this->validate([
                'email' => 'required|email',
                'password' => 'required',
            ], $messages);
        } catch (ValidationException $e) {
            Toaster::error($e->getMessage());
            return;
        }

        $attributes = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        if (! Auth::attempt($attributes, $this->remember)) {
            Toaster::error('Giriş bilgileri hatalı. Lütfen kontrol edip tekrar deneyin.');
            return;
        }

        session()->regenerate();

        return Redirect::route('lobbies')->success(
            'Giriş yapıldı, hoş geldin ' . Auth::user()->name . '! 🎉'
        );
    }

    #[Layout('components.layouts.auth')]
    public function render()
    {
        return view('livewire.auth.pages.login');
    }
}
