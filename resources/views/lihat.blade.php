<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $file->file }}</title>
</head>
<body>
    <form action="/beranda/{{ auth()->id() }}">
        <button>Beranda</button>
    </form>
    <h1>{{ $file->file }}</h1>

    <p>{!!   nl2br(e($teks)) !!}</p>
    
</body>
</html>