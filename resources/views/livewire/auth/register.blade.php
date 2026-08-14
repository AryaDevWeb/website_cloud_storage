<?php

use App\Actions\Auth\RegisterUser;
use App\Livewire\Forms\RegisterForm;
use Illuminate\Auth\Events\Registered;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest', ['title' => 'Daftar'])] class extends Component
{
    public RegisterForm $form;

    /**
     * Handle the registration request.
     *
     * Delegates business logic (user + profile creation, role assignment)
     * to the RegisterUser action which wraps everything in a transaction.
     *
     * After registration the user is NOT logged in automatically.
     * New accounts start as PENDING and require admin approval before
     * they can access the platform.
     */
    public function register(RegisterUser $action): void
    {
        $this->form->validate();

        $user = $action->execute($this->form->toData());

        event(new Registered($user));

        $this->redirectRoute('auth.pending', navigate: true);
    }
}; ?>

<div>
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">
        Buat Akun Baru
    </h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
        Akun akan diverifikasi oleh admin sekolah sebelum dapat digunakan.
    </p>

    <form wire:submit="register" class="space-y-5">
        <!-- Nama Lengkap -->
        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" />
            <x-text-input
                wire:model="form.name"
                id="name"
                class="block mt-1 w-full"
                type="text"
                name="name"
                required
                autofocus
                autocomplete="name"
            />
            <x-input-error :messages="$errors->get('form.name')" class="mt-2" />
        </div>

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
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Daftar Sebagai -->
        <div>
            <x-input-label for="role" :value="__('Daftar Sebagai')" />
            <select
                wire:model.live="form.role"
                id="role"
                name="role"
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                required
            >
                <option value="siswa">Siswa</option>
                <option value="guru">Guru</option>
            </select>
            <x-input-error :messages="$errors->get('form.role')" class="mt-2" />
        </div>

        <!-- NISN (Siswa only) — reactive via Livewire, no custom JS needed -->
        @if ($form->role === 'siswa')
            <div>
                <x-input-label for="nisn" :value="__('NISN')" />
                <x-text-input
                    wire:model="form.nisn"
                    id="nisn"
                    class="block mt-1 w-full"
                    type="text"
                    name="nisn"
                    maxlength="20"
                    required
                />
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Nomor Induk Siswa Nasional (10 digit)</p>
                <x-input-error :messages="$errors->get('form.nisn')" class="mt-2" />
            </div>
        @endif

        <!-- NIP (Guru only) — reactive via Livewire, no custom JS needed -->
        @if ($form->role === 'guru')
            <div>
                <x-input-label for="nip" :value="__('NIP')" />
                <x-text-input
                    wire:model="form.nip"
                    id="nip"
                    class="block mt-1 w-full"
                    type="text"
                    name="nip"
                    maxlength="20"
                    required
                />
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Nomor Induk Pegawai (18 digit)</p>
                <x-input-error :messages="$errors->get('form.nip')" class="mt-2" />
            </div>
        @endif

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
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Konfirmasi Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input
                wire:model="form.password_confirmation"
                id="password_confirmation"
                class="block mt-1 w-full"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('form.password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">
            {{ __('Daftar') }}
        </x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
        Sudah punya akun?
        <a href="{{ route('login') }}" wire:navigate class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 font-medium underline underline-offset-4">
            Masuk
        </a>
    </p>
</div>
