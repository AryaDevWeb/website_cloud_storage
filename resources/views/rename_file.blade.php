<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if (isset($cari_file))
        <title>{{ $cari_file->file }}</title>
    
    @endif
   
</head>
<body>

    @if (isset($cari_file))
        <h1>Rename: {{ $cari_file->file }}</h1>
    
    @endif


    @if (isset($cari_file))
        <form action="/rename_sekarang/{{ $cari_file->id }}">
            <input value="{{ $cari_file->file }}" name="rename" type="text">
            <button>Rename</button>
        </form>
    
    @endif

    <form action="/beranda/{{ auth()->id() }}">
        <button>Beranda</button>
    </form>
    
</body>
</html>