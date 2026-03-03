<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perizinan File</title>
</head>
<body>
    
    <h1>Nama: {{ $cari_file->file }}</h1>

    <form action="/izin_subfile/{{ $cari_file->id }}" method="POST">
        @csrf

        <p>Private</p>
        <input name="izin" type="radio" value="0" {{ $cari_file->izin == 0 ? 'checked' : '' }}>

        <p>Public</p>
        <input name="izin" type="radio" value="1" {{ $cari_file->izin == 1 ? 'checked' : '' }}>

        <button type="submit">Ubah Perizinan</button>
    </form>


    <p>Status File: {{$cari_file->izin == 1 ? 'Public' : 'Private'  }}</p>


    @if (session('status'))
        <p>{{ session('status') }}</p>
    
    @endif

    <form action="/beranda/{{auth()->id() }}">

        <button>Beranda</button>
    </form>
    
</body>
</html>