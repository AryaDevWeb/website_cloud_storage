@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <h2 class="text-lg font-semibold text-white mb-6">Masuk ke Akun</h2>

    <form action="/masuk" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm text-[#94a3b8] mb-1.5">Email</label>
            <input name="email" placeholder="nama@email.com" type="email"
                class="w-full px-3 py-2.5 bg-[#0a0f1e] border border-[#1e293b] rounded-lg text-sm text-[#e2e8f0] placeholder-[#475569] focus:outline-none focus:border-[#3b82f6] transition-colors">
        </div>
        <div>
            <label class="block text-sm text-[#94a3b8] mb-1.5">Password</label>
            <input name="password" placeholder="••••••••" type="password"
                class="w-full px-3 py-2.5 bg-[#0a0f1e] border border-[#1e293b] rounded-lg text-sm text-[#e2e8f0] placeholder-[#475569] focus:outline-none focus:border-[#3b82f6] transition-colors">
        </div>
        <div class="flex items-center gap-2">
            <input name="ingat" type="checkbox" id="ingat"
                class="w-4 h-4 bg-[#0a0f1e] border border-[#1e293b] rounded text-[#3b82f6] focus:ring-0">
            <label for="ingat" class="text-sm text-[#94a3b8]">Ingat Saya</label>
        </div>
        <button type="submit"
            class="w-full py-2.5 bg-[#3b82f6] hover:bg-[#2563eb] text-white text-sm font-medium rounded-lg transition-colors">
            Login
        </button>
    </form>

    <div class="mt-5 pt-5 border-t border-[#1e293b] text-center">
        <p class="text-sm text-[#94a3b8]">Belum punya akun?</p>
        <a href="/" class="inline-block mt-2 text-sm text-[#3b82f6] hover:text-[#60a5fa] transition-colors">
            Daftar Sekarang
        </a>
    </div>

    @error('email')
        <p class="mt-3 text-sm text-red-400">{{ $message }}</p>
    @enderror

    @error('password')
        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
    @enderror

    @if (session('error') && !session('status'))
        <p class="mt-3 text-sm text-red-400">{{ session('error') }}</p>
    @endif
@endsection