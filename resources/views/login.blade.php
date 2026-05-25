@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <h2 class="text-lg font-semibold text-[#0f172a] mb-6">Masuk ke Akun</h2>

    @if (session('error'))
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
            {{ session('error') }}
        </div>
    @endif

    @if (session('status'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-600">
            {{ session('status') }}
        </div>
    @endif

    <form action="/login" method="POST" class="space-y-4 mb-6">
        @csrf
        <div>
            <label for="username" class="block text-sm font-medium text-[#64748b] mb-1.5">Username</label>
            <input type="text" name="username" id="username" placeholder="Masukkan username Anda" required value="{{ old('username') }}"
                class="w-full px-3 py-2.5 bg-white border border-[#e2e8f0] rounded-lg text-sm text-[#0f172a] placeholder-[#94a3b8] focus:outline-none focus:border-[#2563eb] transition-colors">
            @error('username')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-medium text-[#64748b]">Password</label>
            </div>
            <input type="password" name="password" id="password" placeholder="••••••••" required
                class="w-full px-3 py-2.5 bg-white border border-[#e2e8f0] rounded-lg text-sm text-[#0f172a] placeholder-[#94a3b8] focus:outline-none focus:border-[#2563eb] transition-colors">
            @error('password')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center">
            <input type="checkbox" name="ingat" id="ingat" class="h-4 w-4 rounded border-[#e2e8f0] text-[#2563eb] focus:ring-[#2563eb]">
            <label for="ingat" class="ml-2 block text-sm text-[#64748b] select-none cursor-pointer">Ingat saya</label>
        </div>

        <button type="submit"
            class="w-full py-2.5 bg-[#2563eb] hover:bg-[#1d4ed8] text-white text-sm font-medium rounded-lg transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-[#2563eb] focus:ring-offset-2">
            Masuk
        </button>
    </form>

    <div class="relative my-4">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-[#e2e8f0]"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-[#64748b]">atau masuk dengan</span>
        </div>
    </div>

    {{-- Google Login Button --}}
    <a href="/auth/google" 
        class="flex items-center justify-center gap-3 w-full py-2.5 bg-white border border-[#e2e8f0] hover:bg-gray-50 text-sm font-medium rounded-lg transition-colors mb-4">
        <svg class="w-5 h-5" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        Google
    </a>

    <div class="mt-5 pt-5 border-t border-[#e2e8f0] text-center">
        <p class="text-sm text-[#64748b]">Belum punya akun?</p>
        <a href="/register" class="inline-block mt-2 text-sm text-[#2563eb] hover:text-[#1d4ed8] transition-colors font-medium">
            Daftar Sekarang
        </a>
    </div>
@endsection