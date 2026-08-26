<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest', ['title' => 'Masuk'])] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-6">
        Masuk ke Akun
    </h2>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input
                wire:model="form.email"
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input
                wire:model="form.password"
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center gap-2 cursor-pointer">
                <input
                    wire:model="form.remember"
                    id="remember"
                    type="checkbox"
                    class="rounded-md border-zinc-300 dark:border-zinc-700 text-sky-500 shadow-sm focus:ring-sky-400 dark:bg-zinc-800 dark:focus:ring-offset-zinc-900"
                    name="remember"
                >
                <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Ingat saya') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a
                    class="text-sm text-sky-600 hover:text-sky-500 dark:text-sky-400 dark:hover:text-sky-300 underline underline-offset-4 focus:outline-none"
                    href="{{ route('password.request') }}"
                    wire:navigate
                >
                    {{ __('Lupa password?') }}
                </a>
            @endif
        </div>

        <x-primary-button class="w-full justify-center">
            {{ __('Masuk') }}
        </x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Belum punya akun?
        <a href="{{ route('register') }}" wire:navigate class="text-sky-600 hover:text-sky-500 dark:text-sky-400 dark:hover:text-sky-300 font-medium underline underline-offset-4">
            Daftar sekarang
        </a>
    </p>
</div>
