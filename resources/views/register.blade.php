<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>

    @isset($pesan)
        <h1>{{ $pesan }}</h1>
    @endisset

    <h1>Register</h1>

    <form action="/register" method="POST">
        @csrf
        
        <input name="nama" placeholder="Nama" type="text">
        <input name="email" placeholder="Email" type="email">
        <input name="password" placeholder="Password" type="password">
        <input name="password_confirmation" placeholder="Konfirmasi Password" type="password">
        <button type="submit">Register!</button>

    </form>
    <form action="/login" method="GET">
        <button>Login</button>
    </form>

    @if(session('status'))
        <p>{{ session('status') }}</p>
    @endif

    @error('email')
        <p>{{ $message }}</p>
    @enderror
    

    @error('password')
        <p>{{ $message }}</p>
    @enderror

    @error('nama')
        <p>{{ $message }}</p>
    @enderror

    @if (isset($pesan))
        <p>{{ $pesan }}</p>
    
    @endif

</body>
</html>