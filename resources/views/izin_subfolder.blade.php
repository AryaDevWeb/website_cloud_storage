<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Perizinan Folder </title>
    
    
</head>
<body>
    
    
    <h1>Nama: {{ $folder_izin->nama_folder }}</h1>

        <form action="/perizinan_subfolder/{{ $folder_izin->id }}" method="POST">
            @csrf
            <p>Private</p>
            <input name="izin" type="radio" value="0" {{ $folder_izin->permission == 0 ? 'checked' : ' ' }} >
                
            <p>Public</p>
            <input name="izin" type="radio" value="1" {{ $folder_izin->permission == 1 ? 'checked' : ' ' }}>

            <button type="submit">Izin</button>
        </form>


        <p>Status File: {{ $folder_izin->permission == 1 ? 'public' : 'private' }}</p>

    

    @if (session('status'))
        <p>{{ session('status') }}</p>
    
    @endif

    <form action="/beranda/{{ auth()->id() }}">
        <button>Beranda</button>
    </form>

    


    
    
</body>
</html>