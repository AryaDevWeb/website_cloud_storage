<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest', ['title' => 'Lupa Password'])] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink($this->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));
            return;
        }

        $this->reset('email');
        session()->flash('status', __($status));
    }
}; ?>

<div>
    <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-2">
        Lupa Password?
    </h2>
    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-6">
        Masukkan email Anda dan kami akan mengirimkan tautan untuk mengatur ulang password.
    </p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input
                wire:model="email"
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                required
                autofocus
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">
            {{ __('Kirim Tautan Reset') }}
        </x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Ingat password Anda?
        <a href="{{ route('login') }}" wire:navigate class="text-sky-600 hover:text-sky-500 dark:text-sky-400 dark:hover:text-sky-300 font-medium underline underline-offset-4">
            Kembali ke halaman masuk
        </a>
    </p>
</div>
