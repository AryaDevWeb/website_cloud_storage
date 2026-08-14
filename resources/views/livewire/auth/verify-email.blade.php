<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest', ['title' => 'Verifikasi Email'])] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
            return;
        }

        Auth::user()->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
        Verifikasi Email Anda
    </h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
        Terima kasih telah mendaftar! Sebelum melanjutkan, klik tautan verifikasi yang telah dikirim ke email Anda.
        Jika tidak menerima email, kami siap mengirimkannya kembali.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-sm font-medium text-green-700 dark:text-green-400">
                Tautan verifikasi baru telah dikirim ke email Anda.
            </p>
        </div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-primary-button wire:click="sendVerification">
            {{ __('Kirim Ulang Email Verifikasi') }}
        </x-primary-button>

        <button
            wire:click="logout"
            type="button"
            class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 underline underline-offset-4"
        >
            {{ __('Keluar') }}
        </button>
    </div>
</div>
