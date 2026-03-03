<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if (isset($ubah_nama))
        <title>{{ $ubah_nama->file }}</title>
    @endif
    
</head>
<body>
    @if (isset($ubah_nama))
        <h1>Rename File: {{ $ubah_nama->file }}</h1>
    
    @endif

    @if (isset($ubah_nama))
        <form action="/rename/{{ $ubah_nama->id }}">
            <input value="{{ $ubah_nama->nama_tampilan }}" name="ubah_nama" type="text">
            <button>Rename</button>
        </form>
    
    @endif

    <form action="/beranda/{{ auth()->id() }}">
        <button>Beranda</button>
    </form>

  
    
</body>
</html>