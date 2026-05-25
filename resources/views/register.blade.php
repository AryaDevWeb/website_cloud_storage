@extends('layouts.guest')

@section('title', 'Register')

@section('content')
    <h2 class="text-lg font-semibold text-[#0f172a] mb-6">Buat Akun Baru</h2>

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

    <form action="/register" method="POST" class="space-y-4 mb-6">
        @csrf
        
        <div>
            <label for="role_type" class="block text-sm font-medium text-[#64748b] mb-1.5">Mendaftar Sebagai</label>
            <div class="relative">
                <select name="role_type" id="role_type" required onchange="toggleRoleFields()"
                    class="w-full px-3 py-2.5 bg-white border border-[#e2e8f0] rounded-lg text-sm text-[#0f172a] focus:outline-none focus:border-[#2563eb] transition-colors appearance-none pr-10">
                    <option value="siswa" {{ old('role_type') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                    <option value="guru_tendik" {{ old('role_type', 'siswa') == 'guru_tendik' ? 'selected' : '' }}>Guru / Tendik</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-[#64748b]">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                    </svg>
                </div>
            </div>
            @error('role_type')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- SISWA FIELDS --}}
        <div id="siswa_fields" class="space-y-4">
            <div>
                <label for="nama_lengkap" class="block text-sm font-medium text-[#64748b] mb-1.5">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="nama_lengkap" placeholder="Budi Santoso" value="{{ old('nama_lengkap') }}"
                    class="w-full px-3 py-2.5 bg-white border border-[#e2e8f0] rounded-lg text-sm text-[#0f172a] placeholder-[#94a3b8] focus:outline-none focus:border-[#2563eb] transition-colors">
                @error('nama_lengkap')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="nisn" class="block text-sm font-medium text-[#64748b] mb-1.5">NISN</label>
                    <input type="text" name="nisn" id="nisn" placeholder="1234567890" value="{{ old('nisn') }}"
                        class="w-full px-3 py-2.5 bg-white border border-[#e2e8f0] rounded-lg text-sm text-[#0f172a] placeholder-[#94a3b8] focus:outline-none focus:border-[#2563eb] transition-colors">
                    @error('nisn')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="nik" class="block text-sm font-medium text-[#64748b] mb-1.5">NIK (Optional)</label>
                    <input type="text" name="nik" id="nik" placeholder="320101..." value="{{ old('nik') }}"
                        class="w-full px-3 py-2.5 bg-white border border-[#e2e8f0] rounded-lg text-sm text-[#0f172a] placeholder-[#94a3b8] focus:outline-none focus:border-[#2563eb] transition-colors">
                    @error('nik')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <p class="text-xs text-[#64748b] italic">*Masukkan NISN atau NIK untuk validasi dengan Dapodik.</p>
        </div>

        {{-- GURU / TENDIK FIELDS --}}
        <div id="guru_fields" class="space-y-4 hidden">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="nip" class="block text-sm font-medium text-[#64748b] mb-1.5">NIP</label>
                    <input type="text" name="nip" id="nip" placeholder="198001..." value="{{ old('nip') }}"
                        class="w-full px-3 py-2.5 bg-white border border-[#e2e8f0] rounded-lg text-sm text-[#0f172a] placeholder-[#94a3b8] focus:outline-none focus:border-[#2563eb] transition-colors">
                    @error('nip')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="nuptk" class="block text-sm font-medium text-[#64748b] mb-1.5">NUPTK</label>
                    <input type="text" name="nuptk" id="nuptk" placeholder="889988..." value="{{ old('nuptk') }}"
                        class="w-full px-3 py-2.5 bg-white border border-[#e2e8f0] rounded-lg text-sm text-[#0f172a] placeholder-[#94a3b8] focus:outline-none focus:border-[#2563eb] transition-colors">
                    @error('nuptk')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <p class="text-xs text-[#64748b] italic">*Guru/Tendik divalidasi menggunakan email Dapodik dan NIP/NUPTK.</p>
        </div>

        {{-- COMMON SHARED FIELDS --}}
        <div class="border-t border-[#e2e8f0] pt-4 space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-[#64748b] mb-1.5">Username</label>
                <input type="text" name="username" id="username" placeholder="Pilih username unik" required value="{{ old('username') }}"
                    class="w-full px-3 py-2.5 bg-white border border-[#e2e8f0] rounded-lg text-sm text-[#0f172a] placeholder-[#94a3b8] focus:outline-none focus:border-[#2563eb] transition-colors">
                @error('username')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-[#64748b] mb-1.5">Email</label>
                <input type="email" name="email" id="email" placeholder="nama@email.com" required value="{{ old('email') }}"
                    class="w-full px-3 py-2.5 bg-white border border-[#e2e8f0] rounded-lg text-sm text-[#0f172a] placeholder-[#94a3b8] focus:outline-none focus:border-[#2563eb] transition-colors">
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-[#64748b] mb-1.5">Password</label>
                    <input type="password" name="password" id="password" placeholder="••••••••" required
                        class="w-full px-3 py-2.5 bg-white border border-[#e2e8f0] rounded-lg text-sm text-[#0f172a] placeholder-[#94a3b8] focus:outline-none focus:border-[#2563eb] transition-colors">
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-[#64748b] mb-1.5">Konfirmasi</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="••••••••" required
                        class="w-full px-3 py-2.5 bg-white border border-[#e2e8f0] rounded-lg text-sm text-[#0f172a] placeholder-[#94a3b8] focus:outline-none focus:border-[#2563eb] transition-colors">
                </div>
            </div>
        </div>

        <button type="submit"
            class="w-full py-2.5 bg-[#2563eb] hover:bg-[#1d4ed8] text-white text-sm font-medium rounded-lg transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-[#2563eb] focus:ring-offset-2">
            Daftar Akun
        </button>
    </form>

    <div class="relative my-4">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-[#e2e8f0]"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-[#64748b]">atau daftar dengan</span>
        </div>
    </div>

    {{-- Google Register Button --}}
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
        <p class="text-sm text-[#64748b]">Sudah punya akun?</p>
        <a href="/login" class="inline-block mt-2 text-sm text-[#2563eb] hover:text-[#1d4ed8] transition-colors font-medium">
            Login Sekarang
        </a>
    </div>

    <script>
        function toggleRoleFields() {
            const roleType = document.getElementById('role_type').value;
            const siswaFields = document.getElementById('siswa_fields');
            const guruFields = document.getElementById('guru_fields');
            
            if (roleType === 'siswa') {
                siswaFields.classList.remove('hidden');
                guruFields.classList.add('hidden');
                
                document.querySelectorAll('#siswa_fields input').forEach(input => input.disabled = false);
                document.querySelectorAll('#guru_fields input').forEach(input => input.disabled = true);
            } else {
                siswaFields.classList.add('hidden');
                guruFields.classList.remove('hidden');
                
                document.querySelectorAll('#siswa_fields input').forEach(input => input.disabled = true);
                document.querySelectorAll('#guru_fields input').forEach(input => input.disabled = false);
            }
        }

        // Initialize state on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleRoleFields();
        });
    </script>
@endsection