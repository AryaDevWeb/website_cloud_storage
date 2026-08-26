<div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    @forelse ($files as $file)
        <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-md">
            <button type="button" class="relative flex h-40 w-full items-center justify-center overflow-hidden bg-slate-50" @click="preview = { open: true, url: '{{ $this->previewUrl($file->id) }}', name: @js($file->file_name), mime: @js($file->mime_type), kind: @js(in_array($file->mime_type, ['image/png', 'image/jpeg', 'application/pdf'], true) ? 'preview' : 'unsupported') }">
                @if (in_array($file->mime_type, ['image/png', 'image/jpeg'], true))
                    <img src="{{ $this->previewUrl($file->id) }}" alt="{{ $file->file_name }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                @else
                    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-100 text-xs font-black tracking-wider text-indigo-700">{{ $this->fileIcon($file->file_name) }}</span>
                @endif
                <span class="absolute right-3 top-3 rounded-full bg-slate-950/60 px-2.5 py-1 text-[10px] font-semibold text-white opacity-0 transition group-hover:opacity-100">Preview</span>
            </button>
            <div class="p-4">
                <p class="truncate text-sm font-semibold text-slate-800" title="{{ $file->file_name }}">{{ $file->file_name }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ $this->formatBytes($file->size) }} · {{ $file->created_at?->diffForHumans() }}</p>
                <div class="mt-4 flex items-center gap-2">
                    <a href="{{ $this->downloadUrl($file->id) }}" class="btn-secondary flex-1 px-3 py-2 text-center text-xs">Download</a>
                    <button wire:click="prepareShare({{ $file->id }})" class="rounded-xl p-2 text-slate-400 hover:bg-indigo-50 hover:text-indigo-600" title="Bagikan">↗</button>
                    <button wire:click="deleteFile({{ $file->id }})" wire:confirm="Pindahkan file ke Recycle Bin?" class="rounded-xl p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="Hapus">⌫</button>
                </div>
            </div>
        </article>
    @empty
        <div class="p-12 text-center sm:col-span-2 lg:col-span-3 xl:col-span-4">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-2xl text-indigo-500">↑</div>
            <p class="mt-4 text-sm font-semibold text-slate-700">Belum ada file di sini</p>
            <p class="mt-1 text-sm text-slate-400">Upload dokumen pertama Anda untuk mulai bekerja.</p>
        </div>
    @endforelse
</div>
