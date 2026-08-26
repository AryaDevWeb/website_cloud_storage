<?php

use App\Models\FileShare;
use App\Models\Folder;
use App\Models\Media;
use App\Models\StorageAuditLog;
use App\Models\StorageQuota;
use App\Models\User;
use App\Services\FileSharingService;
use App\Services\StorageService;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public string $section = 'files';
    public string $search = '';
    public ?int $currentFolderId = null;
    public $upload;
    public ?int $uploadFolderId = null;
    public string $newFolderName = '';
    public bool $folderModal = false;
    public bool $shareModal = false;
    public ?int $sharingMediaId = null;
    public string $shareTarget = 'user';
    public ?int $shareRecipientId = null;
    public string $shareRole = '';
    public string $sharePermission = 'download';
    public string $shareExpiresAt = '';

    public function mount(?string $section = 'files'): void
    {
        $section ??= request()->query('section', 'files');

        $this->section = in_array($section, ['files', 'shared', 'sent', 'trash', 'audit'], true)
            ? $section
            : 'files';
    }

    #[Computed]
    public function quota(): StorageQuota
    {
        return auth()->user()->storageQuota()->firstOrCreate([
            'user_id' => auth()->id(),
        ], [
            'max_bytes' => StorageQuota::defaultMaxBytesFor(auth()->user()),
            'used_bytes' => 0,
        ]);
    }

    #[Computed]
    public function folderChildren()
    {
        return Folder::query()
            ->where('user_id', auth()->id())
            ->where('parent_id', $this->currentFolderId)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function currentFolder(): ?Folder
    {
        return $this->currentFolderId
            ? Folder::query()->where('user_id', auth()->id())->find($this->currentFolderId)
            : null;
    }

    #[Computed]
    public function folders()
    {
        return Folder::query()->where('user_id', auth()->id())->orderBy('name')->get();
    }

    #[Computed]
    public function files()
    {
        return Media::query()
            ->where('model_type', User::class)
            ->where('model_id', auth()->id())
            ->where('collection_name', 'files')
            ->when($this->currentFolderId, fn ($query) => $query->where('folder_id', $this->currentFolderId), fn ($query) => $query->whereNull('folder_id'))
            ->when($this->search !== '', fn ($query) => $query->where(function ($query) {
                $query->where('file_name', 'like', '%'.$this->search.'%')
                    ->orWhere('name', 'like', '%'.$this->search.'%');
            }))
            ->latest()
            ->get();
    }

    #[Computed]
    public function sharedFiles()
    {
        $roles = auth()->user()->getRoleNames();

        return FileShare::query()
            ->active()
            ->where(function ($query) use ($roles) {
                $query->where('shared_to_user_id', auth()->id())
                    ->orWhereIn('shared_to_role', $roles);
            })
            ->with(['media', 'sharedBy'])
            ->latest()
            ->get();
    }

    #[Computed]
    public function sentShares()
    {
        return FileShare::query()->where('shared_by_user_id', auth()->id())->with(['media', 'sharedTo'])->latest()->get();
    }

    #[Computed]
    public function trashedFiles()
    {
        return Media::onlyTrashed()
            ->where('model_type', User::class)
            ->where('model_id', auth()->id())
            ->latest('deleted_at')
            ->get();
    }

    #[Computed]
    public function auditLogs()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return StorageAuditLog::query()->with(['user', 'media'])->latest()->limit(100)->get();
    }

    #[Computed]
    public function recipients()
    {
        return User::query()
            ->where('id', '<>', auth()->id())
            ->where('status', 'active')
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['guru', 'siswa']))
            ->orderBy('name')
            ->get();
    }

    public function openFolder(int $folderId): void
    {
        $this->currentFolderId = Folder::query()->where('user_id', auth()->id())->findOrFail($folderId)->id;
        unset($this->folderChildren, $this->currentFolder, $this->files);
    }

    public function openRoot(): void
    {
        $this->currentFolderId = null;
        unset($this->folderChildren, $this->currentFolder, $this->files);
    }

    public function createFolder(): void
    {
        $this->validate(['newFolderName' => ['required', 'string', 'max:120']]);

        $name = trim($this->newFolderName);
        Folder::create([
            'user_id' => auth()->id(),
            'parent_id' => $this->currentFolderId,
            'name' => $name,
            'slug' => Str::slug($name),
        ]);

        $this->reset('newFolderName', 'folderModal');
        unset($this->folderChildren, $this->folders);
        session()->flash('status', 'Folder baru berhasil dibuat.');
    }

    public function uploadFile(): void
    {
        $this->validate(['upload' => ['required', 'file', 'max:10240']]);

        $folder = $this->uploadFolderId
            ? Folder::query()->where('user_id', auth()->id())->findOrFail($this->uploadFolderId)
            : null;

        app(StorageService::class)->upload(auth()->user(), $this->upload, $folder);
        $this->reset('upload', 'uploadFolderId');
        unset($this->files, $this->quota);
        session()->flash('status', 'File berhasil diunggah dengan aman.');
    }

    public function prepareShare(int $mediaId): void
    {
        Media::query()->where('model_type', User::class)->where('model_id', auth()->id())->findOrFail($mediaId);
        $this->sharingMediaId = $mediaId;
        $this->reset('shareRecipientId', 'shareRole', 'shareExpiresAt');
        $this->shareTarget = 'user';
        $this->sharePermission = 'download';
        $this->shareModal = true;
    }

    public function shareFile(): void
    {
        $this->validate([
            'sharePermission' => ['required', 'in:view,download'],
            'shareRecipientId' => ['nullable', 'integer'],
            'shareRole' => ['nullable', 'in:guru,siswa'],
            'shareExpiresAt' => ['nullable', 'date'],
        ]);

        $media = Media::query()->where('model_type', User::class)->where('model_id', auth()->id())->findOrFail($this->sharingMediaId);
        $expiresAt = $this->shareExpiresAt !== '' ? Carbon::parse($this->shareExpiresAt) : null;

        app(FileSharingService::class)->share(
            auth()->user(),
            $media,
            $this->shareTarget === 'user' ? $this->shareRecipientId : null,
            $this->shareTarget === 'role' ? $this->shareRole : null,
            $this->sharePermission,
            $expiresAt,
        );

        $this->reset('shareModal', 'sharingMediaId', 'shareRecipientId', 'shareRole', 'shareExpiresAt');
        unset($this->sentShares);
        session()->flash('status', 'File berhasil dibagikan.');
    }

    public function deleteFile(int $mediaId): void
    {
        $media = Media::query()->where('model_type', User::class)->where('model_id', auth()->id())->findOrFail($mediaId);
        app(StorageService::class)->delete($media, auth()->user());
        unset($this->files, $this->quota);
        session()->flash('status', 'File dipindahkan ke Recycle Bin.');
    }

    public function restoreFile(int $mediaId): void
    {
        $media = Media::onlyTrashed()->where('model_type', User::class)->where('model_id', auth()->id())->findOrFail($mediaId);
        app(StorageService::class)->restore($media, auth()->user());
        unset($this->trashedFiles, $this->quota);
        session()->flash('status', 'File berhasil dipulihkan.');
    }

    public function permanentlyDeleteFile(int $mediaId): void
    {
        $media = Media::onlyTrashed()->where('model_type', User::class)->where('model_id', auth()->id())->findOrFail($mediaId);
        app(StorageService::class)->permanentlyDelete($media, auth()->user());
        unset($this->trashedFiles);
        session()->flash('status', 'File dihapus permanen.');
    }

    public function downloadUrl(int $mediaId): string
    {
        return URL::temporarySignedRoute('files.download', now()->addMinutes(10), ['media' => $mediaId]);
    }

    public function previewUrl(int $mediaId): string
    {
        return URL::temporarySignedRoute('files.download', now()->addMinutes(10), [
            'media' => $mediaId,
            'preview' => 1,
        ]);
    }

    public function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return $bytes.' B';
        if ($bytes < 1048576) return number_format($bytes / 1024, 1).' KB';
        if ($bytes < 1073741824) return number_format($bytes / 1048576, 1).' MB';

        return number_format($bytes / 1073741824, 1).' GB';
    }

    public function fileIcon(string $fileName): string
    {
        return match (strtolower(pathinfo($fileName, PATHINFO_EXTENSION))) {
            'pdf' => 'PDF', 'docx' => 'DOC', 'xlsx' => 'XLS', 'zip' => 'ZIP', 'png', 'jpg', 'jpeg' => 'IMG', default => 'FILE',
        };
    }
}; ?>

<div x-data="{ folderModal: @entangle('folderModal'), shareModal: @entangle('shareModal'), preview: { open: false, url: '', name: '', mime: '', kind: '' } }" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col justify-between gap-5 md:flex-row md:items-end">
        <div>
            <p class="eyebrow">Cloud storage sekolah</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Ruang file Anda</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">Simpan, atur, dan bagikan dokumen sekolah dengan kontrol akses yang aman.</p>
        </div>
        <div class="flex gap-3">
            <button @click="folderModal = true" class="btn-secondary"><span class="mr-2 text-lg">+</span> Folder</button>
            <label class="btn-primary cursor-pointer">
                <span class="mr-2 text-lg">↑</span> Upload file
                <input type="file" wire:model="upload" wire:change="uploadFile" class="hidden" accept=".pdf,.docx,.xlsx,.png,.jpg,.jpeg,.zip">
            </label>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('status') }}</div>
    @endif

    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="surface p-5"><p class="text-sm text-slate-500">Total digunakan</p><p class="mt-2 text-2xl font-bold text-slate-950">{{ $this->formatBytes($this->quota->used_bytes) }}</p><p class="mt-1 text-xs text-slate-400">dari {{ $this->formatBytes($this->quota->max_bytes) }}</p></div>
        <div class="surface p-5"><p class="text-sm text-slate-500">File aktif</p><p class="mt-2 text-2xl font-bold text-slate-950">{{ $this->files->count() }}</p><p class="mt-1 text-xs text-slate-400">di folder ini</p></div>
        <div class="surface p-5"><p class="text-sm text-slate-500">Dibagikan kepada saya</p><p class="mt-2 text-2xl font-bold text-slate-950">{{ $this->sharedFiles->count() }}</p><p class="mt-1 text-xs text-slate-400">share yang masih aktif</p></div>
        <div class="surface p-5"><p class="text-sm text-slate-500">Recycle bin</p><p class="mt-2 text-2xl font-bold text-slate-950">{{ $this->trashedFiles->count() }}</p><p class="mt-1 text-xs text-slate-400">menunggu tindakan</p></div>
    </div>

    <div class="mb-8 surface p-5 sm:p-6">
        <div class="flex items-center justify-between gap-4"><div><p class="text-sm font-semibold text-slate-800">Kuota penyimpanan</p><p class="mt-1 text-xs text-slate-500">{{ $this->formatBytes($this->quota->max_bytes - $this->quota->used_bytes) }} tersisa</p></div><span class="text-sm font-bold text-indigo-600">{{ $this->quota->max_bytes > 0 ? number_format(min(100, ($this->quota->used_bytes / $this->quota->max_bytes) * 100), 1) : 0 }}%</span></div>
        <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-indigo-600 transition-all" style="width: {{ $this->quota->max_bytes > 0 ? min(100, ($this->quota->used_bytes / $this->quota->max_bytes) * 100) : 0 }}%"></div></div>
    </div>

    <div class="mb-5 flex flex-wrap gap-2">
        @foreach ([['files', 'File Saya'], ['shared', 'Dibagikan ke Saya'], ['sent', 'Share Saya'], ['trash', 'Recycle Bin']] as [$key, $label])
            <a href="{{ route('storage.index', ['section' => $key]) }}" wire:navigate class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ $section === $key ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-500 hover:bg-indigo-50 hover:text-indigo-700' }}">{{ $label }}</a>
        @endforeach
        @if (auth()->user()->isAdmin())
            <a href="{{ route('storage.index', ['section' => 'audit']) }}" wire:navigate class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ $section === 'audit' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-500 hover:bg-indigo-50 hover:text-indigo-700' }}">Audit Log</a>
        @endif
    </div>

    @if ($section === 'files')
        <div class="surface overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2 text-sm font-semibold text-slate-700"><button wire:click="openRoot" class="hover:text-indigo-600">My Files</button>@if ($this->currentFolder)<span class="text-slate-300">/</span><span class="text-indigo-600">{{ $this->currentFolder->name }}</span>@endif</div>
                <input wire:model.live="search" class="field max-w-xs" placeholder="Cari file...">
            </div>
            <div class="grid gap-3 border-b border-slate-100 bg-slate-50/70 p-5 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($this->folderChildren as $folder)
                    <button wire:click="openFolder({{ $folder->id }})" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-indigo-300 hover:shadow-sm"><span class="text-xl text-amber-500">▰</span><span class="truncate text-sm font-semibold text-slate-700">{{ $folder->name }}</span></button>
                @empty
                    @if (!$this->files->count()) <p class="text-sm text-slate-400 sm:col-span-2 lg:col-span-4">Belum ada folder atau file di lokasi ini.</p> @endif
                @endforelse
            </div>
            @include('livewire.storage.file-grid', ['files' => $this->files])
            <div class="hidden divide-y divide-slate-100">
                @forelse ($this->files as $file)
                    <div class="flex flex-col gap-4 p-5 transition hover:bg-slate-50 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-4"><span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-[10px] font-bold text-indigo-600">{{ $this->fileIcon($file->file_name) }}</span><div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-800">{{ $file->file_name }}</p><p class="mt-1 text-xs text-slate-400">{{ $this->formatBytes($file->size) }} · {{ $file->created_at?->diffForHumans() }}</p></div></div>
                        <div class="flex items-center gap-2"><a href="{{ $this->downloadUrl($file->id) }}" class="btn-secondary px-3 py-2 text-xs">Download</a><button wire:click="prepareShare({{ $file->id }})" class="rounded-xl p-2 text-slate-400 hover:bg-indigo-50 hover:text-indigo-600" title="Bagikan">↗</button><button wire:click="deleteFile({{ $file->id }})" wire:confirm="Pindahkan file ke Recycle Bin?" class="rounded-xl p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="Hapus">⌫</button></div>
                    </div>
                @empty
                    <div class="p-12 text-center"><div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-2xl text-indigo-500">↑</div><p class="mt-4 text-sm font-semibold text-slate-700">Belum ada file di sini</p><p class="mt-1 text-sm text-slate-400">Upload dokumen pertama Anda untuk mulai bekerja.</p></div>
                @endforelse
            </div>
        </div>
    @elseif ($section === 'shared')
        <div class="surface overflow-hidden"><div class="border-b border-slate-100 p-5"><h2 class="font-semibold text-slate-900">Dibagikan ke saya</h2><p class="mt-1 text-sm text-slate-500">File yang dapat Anda akses dari pengguna lain.</p></div><div class="divide-y divide-slate-100">@forelse ($this->sharedFiles as $share)<div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-sm font-semibold text-slate-800">{{ $share->media?->file_name }}</p><p class="mt-1 text-xs text-slate-400">Dari {{ $share->sharedBy?->name }} · {{ $share->expires_at ? 'berakhir '.$share->expires_at->diffForHumans() : 'tanpa batas waktu' }}</p></div><a href="{{ $this->downloadUrl($share->media_id) }}" class="btn-primary px-3 py-2 text-xs">Download</a></div>@empty<div class="p-12 text-center text-sm text-slate-400">Belum ada file yang dibagikan kepada Anda.</div>@endforelse</div></div>
    @elseif ($section === 'sent')
        <div class="surface overflow-hidden"><div class="border-b border-slate-100 p-5"><h2 class="font-semibold text-slate-900">Share saya</h2><p class="mt-1 text-sm text-slate-500">Pantau file yang pernah Anda bagikan.</p></div><div class="divide-y divide-slate-100">@forelse ($this->sentShares as $share)<div class="flex items-center justify-between gap-4 p-5"><div><p class="text-sm font-semibold text-slate-800">{{ $share->media?->file_name }}</p><p class="mt-1 text-xs text-slate-400">{{ $share->sharedTo?->name ?? 'Role: '.$share->shared_to_role }} · {{ $share->permission }} · {{ $share->expires_at ? $share->expires_at->format('d M Y H:i') : 'tanpa batas waktu' }}</p></div><span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Aktif</span></div>@empty<div class="p-12 text-center text-sm text-slate-400">Belum ada file yang Anda bagikan.</div>@endforelse</div></div>
    @elseif ($section === 'trash')
        <div class="surface overflow-hidden"><div class="border-b border-slate-100 p-5"><h2 class="font-semibold text-slate-900">Recycle Bin</h2><p class="mt-1 text-sm text-slate-500">File akan dibersihkan otomatis setelah 30 hari.</p></div><div class="divide-y divide-slate-100">@forelse ($this->trashedFiles as $file)<div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-sm font-semibold text-slate-800">{{ $file->file_name }}</p><p class="mt-1 text-xs text-slate-400">Dihapus {{ $file->deleted_at?->diffForHumans() }} · {{ $this->formatBytes($file->size) }}</p></div><div class="flex gap-2"><button wire:click="restoreFile({{ $file->id }})" class="btn-secondary px-3 py-2 text-xs">Pulihkan</button><button wire:click="permanentlyDeleteFile({{ $file->id }})" wire:confirm="Hapus file secara permanen?" class="rounded-xl px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50">Hapus permanen</button></div></div>@empty<div class="p-12 text-center text-sm text-slate-400">Recycle Bin kosong.</div>@endforelse</div></div>
    @elseif ($section === 'audit' && auth()->user()->isAdmin())
        <div class="surface overflow-hidden"><div class="border-b border-slate-100 p-5"><h2 class="font-semibold text-slate-900">Audit Log Storage</h2><p class="mt-1 text-sm text-slate-500">100 aktivitas terbaru di ruang penyimpanan.</p></div><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3">User</th><th class="px-5 py-3">Aksi</th><th class="px-5 py-3">File</th><th class="px-5 py-3">IP</th><th class="px-5 py-3">Waktu</th></tr></thead><tbody class="divide-y divide-slate-100">@foreach ($this->auditLogs as $log)<tr><td class="px-5 py-4 font-medium text-slate-700">{{ $log->user?->email ?? 'System' }}</td><td class="px-5 py-4"><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ $log->action }}</span></td><td class="px-5 py-4 text-slate-500">{{ $log->media?->file_name ?? '—' }}</td><td class="px-5 py-4 text-slate-500">{{ $log->ip_address ?? '—' }}</td><td class="whitespace-nowrap px-5 py-4 text-slate-400">{{ $log->created_at?->diffForHumans() }}</td></tr>@endforeach</tbody></table></div></div>
    @endif

    <div x-show="folderModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 px-4" @keydown.escape.window="folderModal = false"><div @click.outside="folderModal = false" class="surface w-full max-w-md p-6"><div class="flex items-center justify-between"><h2 class="text-lg font-bold text-slate-900">Folder baru</h2><button @click="folderModal = false" class="text-slate-400">×</button></div><form wire:submit="createFolder" class="mt-6 space-y-4"><input wire:model="newFolderName" class="field" placeholder="Nama folder" autofocus>@error('newFolderName')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror<div class="flex justify-end gap-3"><button type="button" @click="folderModal = false" class="btn-secondary">Batal</button><button class="btn-primary">Buat folder</button></div></form></div></div>

    <div x-show="shareModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 px-4" @keydown.escape.window="shareModal = false"><div @click.outside="shareModal = false" class="surface w-full max-w-lg p-6"><div class="flex items-center justify-between"><div><p class="eyebrow">Kolaborasi</p><h2 class="mt-1 text-lg font-bold text-slate-900">Bagikan file</h2></div><button @click="shareModal = false" class="text-slate-400">×</button></div><form wire:submit="shareFile" class="mt-6 space-y-4"><div class="grid grid-cols-2 gap-2 rounded-xl bg-slate-100 p-1"><button type="button" wire:click="$set('shareTarget', 'user')" class="rounded-lg px-3 py-2 text-sm font-semibold {{ $shareTarget === 'user' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500' }}">Orang tertentu</button><button type="button" wire:click="$set('shareTarget', 'role')" class="rounded-lg px-3 py-2 text-sm font-semibold {{ $shareTarget === 'role' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500' }}">Berdasarkan role</button></div>@if ($shareTarget === 'user')<select wire:model="shareRecipientId" class="field"><option value="">Pilih penerima</option>@foreach ($this->recipients as $recipient)<option value="{{ $recipient->id }}">{{ $recipient->name }} · {{ $recipient->email }}</option>@endforeach</select>@else<select wire:model="shareRole" class="field"><option value="">Pilih role</option><option value="guru">Semua Guru</option><option value="siswa">Semua Siswa</option></select>@endif<select wire:model="sharePermission" class="field"><option value="download">Dapat mengunduh</option><option value="view">Hanya melihat</option></select><label class="block text-sm font-medium text-slate-700">Berlaku sampai <input type="datetime-local" wire:model="shareExpiresAt" class="field mt-2"><span class="mt-1 block text-xs font-normal text-slate-400">Kosongkan jika tidak memiliki batas waktu.</span></label>@error('recipient')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror @error('shared_to_user_id')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror @error('shared_to_role')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror<div class="flex justify-end gap-3"><button type="button" @click="shareModal = false" class="btn-secondary">Batal</button><button class="btn-primary">Bagikan file</button></div></form></div></div>
    <div x-show="preview.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4" @keydown.escape.window="preview.open = false">
        <div @click.outside="preview.open = false" class="max-h-[92vh] w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div class="min-w-0"><p class="truncate text-sm font-bold text-slate-900" x-text="preview.name"></p><p class="mt-1 text-xs text-slate-400" x-text="preview.mime"></p></div>
                <div class="flex items-center gap-2"><a :href="preview.url" class="btn-secondary px-3 py-2 text-xs">Download</a><button @click="preview.open = false" class="rounded-xl p-2 text-xl leading-none text-slate-400 hover:bg-slate-100">×</button></div>
            </div>
            <div class="flex max-h-[calc(92vh-76px)] min-h-[360px] items-center justify-center bg-slate-100 p-4">
                <template x-if="preview.kind === 'preview' && preview.mime.startsWith('image/')"><img :src="preview.url" :alt="preview.name" class="max-h-[calc(92vh-120px)] max-w-full rounded-xl object-contain shadow-lg"></template>
                <template x-if="preview.kind === 'preview' && preview.mime === 'application/pdf'"><iframe :src="preview.url" title="File preview" class="h-[70vh] w-full rounded-xl bg-white"></iframe></template>
                <template x-if="preview.kind === 'unsupported'"><div class="text-center"><div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-indigo-100 text-xl font-black text-indigo-700">FILE</div><p class="mt-4 font-semibold text-slate-700">Preview browser tidak tersedia</p><p class="mt-1 text-sm text-slate-500">Gunakan tombol Download untuk membuka file ini.</p></div></template>
            </div>
        </div>
    </div>
</div>
