<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>perizinan Folder</title>
</head>
<body>
    <h1>{{ $izin_folder->nama_folder }}</h1>

    <form action="/folder_permission/{{ $izin_folder->id }}" method="POST">
        @csrf
        <input type="radio" name="izin" value="0" {{ $izin_folder->permission == 0 ? 'checked' : '' }} >
        <p>Private</p>
        <input type="radio" name="izin" value="1" {{ $izin_folder->permission == 1 ? 'checked' : '' }}>
        <p>Public</p>

        <button>Ubah Perizinan</button>
    </form>

    @if (isset($izin_folder->permission))
        <p>Status File: {{ $izin_folder->permission == 1 ? 'Public' : 'Private' }}</p>
    
    @endif

    @if (session('status'))
        <h3>{{ session('status') }}</h3>
    
    @endif

    <form action="/beranda/{{ auth()->id() }}">
        <button>Beranda</button>
    </form>
    
</body>
</html>