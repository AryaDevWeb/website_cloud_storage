<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if (isset($cari_folder))
        <title>{{ $cari_folder->nama_folder }}</title>
    
    @endif
    
</head>
<body>

    @if (isset($cari_folder))
        <h1>Rename: {{$cari_folder->nama_folder  }}</h1>
    
    @endif

    @if (isset($pesan_berubah))
        <p>{{ $pesan_berubah }}</p>
    
    @endif

    @if (isset($cari_folder))
        <form action="/rename_f/{{ $cari_folder->id }}">
            <input name="rename" type="text">
            <button>Rename</button>
        </form>

    
    @endif

 
    <form action="/beranda/{{ auth()->id() }}">
        <button>Beranda</button>

    </form>
    
</body>
</html>