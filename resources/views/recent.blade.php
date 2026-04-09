<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recent</title>
</head>
<body>

    @foreach ($file as $file_terbaru )
        <p>Nama: {{ $file_terbaru->nama_tampilan }}</p>
        <p>Tanggal: {{ \Carbon\Carbon::parse($file_terbaru->riwayat)->diffForHumans() }}</p>    
    @endforeach
    
</body>
</html>