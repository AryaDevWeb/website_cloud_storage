<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $file->nama_tampilan }}</title>
</head>
<body>
    <form action="/beranda/{{ auth()->id() }}">
        <button>Beranda</button>
    </form>
    <h1>{{ $file->nama_tampilan }}</h1>

    <p>{!!   nl2br(e($teks)) !!}</p>
    
</body>
</html>