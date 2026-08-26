<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest', ['title' => 'Konfirmasi Password'])] class extends Component
{
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email'    => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-2">
        Konfirmasi Password
    </h2>
    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-6">
        Ini adalah area aman. Konfirmasi password Anda sebelum melanjutkan.
    </p>

    <form wire:submit="confirmPassword" class="space-y-5">
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input
                wire:model="password"
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autofocus
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">
            {{ __('Konfirmasi') }}
        </x-primary-button>
    </form>
</div>
