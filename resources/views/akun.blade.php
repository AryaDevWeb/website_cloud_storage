<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if (isset($lihat_akun) )
        <title>{{ $lihat_akun->name }}</title>
    
    @endif
    
</head>
<body>

    @if (isset($lihat_akun) )
         <h1>{{ $lihat_akun->name }}</h1>
    @endif

    <form action="/beranda/{{ auth()->id() }}">
        <button>Beranda</button>
    </form>


    <form action="/hapus_akun/{{ auth()->id() }}">
        <button>Hapus Akun</button>
    </form>
    <form action="/logout" method="POST">
        @csrf
        <button type="submit">Log Out</button>
    </form>
   
    
</body>
</html>