<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>

    <form action="/masuk" method="POST">
        @csrf
        <input name="email" placeholder="Email" type="email">
        <input name="password" placeholder="Password" type="password">
        <input name="ingat" type="checkbox">
        <label for="ingat">Ingat Saya</label>
        <button type="submit">Login</button>
        
    </form>

    <form action="/">
        <button>Register</button>
    </form>


    @if (session('error') && !session('status'))
        <p>{{ session('error') }}</p>
    
    @endif

    @error('email')
        <p>{{ $message }}</p>
    @enderror

    @error('password')
        <p>{{ $message }}</p>
    @enderror


</body>
</html>