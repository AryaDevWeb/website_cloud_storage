@extends('layouts.app')
@section('title', 'My Files')

@section('content')
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tempat Sampah</title>
</head>
<body>
    @if (session("status"))
        <p>{{ session("status") }}</p>
    
    @endif

    <h1>Tempat Sampah: </h1>
    @foreach ($file_sampah as $sampah )
        <button>{{ $sampah->nama_tampilan }}</button>
        <p>Tanggal: {{ $sampah->deleted_at->diffForHumans() }}</p>


        <form action="/restore/{{ $sampah->id }}">
            <button>Restore</button>

        </form>
  

        <form action="/hapus_permanen/{{ $sampah->id }}">
            <button>Delete</button>

        </form>
     
    
    @endforeach


    
</body>
</html>

@endsection

