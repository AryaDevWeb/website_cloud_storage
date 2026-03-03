<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perizinan File</title>
</head>
<body>

    <h1>Nama: {{ $isi_file->file }}</h1>

    <form action="/ubah_perizinan/{{ $isi_file->id }}" method="POST">
        @csrf
        <input type="radio" name="izin" value="0" {{ $isi_file->izin == 0 ? 'checked' : '' }}>
        <p>Private</p>
        <input type="radio" name="izin" value="1" {{ $isi_file->izin == 1 ? 'checked' : '' }}>
        <p>Public</p>
        
        <button type="submit">Ubah Perizinan</button>

    </form>

    @if (isset($isi_file->izin))
        <p>Status File: {{ $isi_file->izin == 1 ? 'public' : 'private'}}</p>
    
    @endif

    @if (session('status'))
        <h3>{{ session('status') }}</h3>
    
    @endif
    
    <form action="/beranda/{{ auth()->id() }}">
        <button>Beranda</button>
    </form>


</body>
</html>