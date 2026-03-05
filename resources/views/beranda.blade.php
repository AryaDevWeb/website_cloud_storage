<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda</title>
</head>
<body>
    
    <h1>Beranda</h1>

    @if(isset($user) || isset($folders) || isset($files) )
        <form action="/lihat_akun/{{ auth()->id() }}">
            <button><h2>Nama: {{ $user->name }}</h2></button>
            

        </form>
        
    
    @endif

    @if (session('error'))
        <p>{{ session('error') }}</p>
    
    @endif


    @if (session('status'))
        <p>{{ session('status') }}</p>
    
    @endif

    @isset($user->wallets)
        <h3>Koin: {{$user->wallets->koin  }}</h3>
    @else
        <h3>Koin: 0</h3>
    @endif

    <form action="/pencarian">
        <input name="cari" placeholder="Cari" type="text">
        <button>Cari</button>

    </form>


    @if ( isset($folders) && $folders->count() ||  isset($files) && $files->count())
        @foreach ($folders as $isi_folder )
            <button>{{ $isi_folder->nama_folder }}</button>
            <form action="/hapus_folder/{{ $isi_folder->id }}">
                <button>Hapus</button>
            </form>
        
        @endforeach

        @foreach ($files as $isi_file )
            <button>{{ $isi_file->nama_tampilan }}</button>  
            <form action="/hapus/{{ $isi_file->id }}">
                <button>Hapus</button>
            </form>      
        @endforeach
    
    @endif

    @if (isset($status_rename))
        <p>{{ $status_rename }}</p>
    
    @endif

  

    @if( isset($folders) && isset($file) && $files->isEmpty() && $folders->isEmpty())
        <p>Data tidak ada</p>
    @endif


    <form action="/upload" method="POST" enctype="multipart/form-data">
        @csrf
        <input name="upload" type="file">
        <button>Upload File</button>

    </form>

    @if(session('nama_tampil'))
        <p>File {{ session('nama_tampil') }} berhasil ditambahkan!</p>

    @endif



        
        @foreach (auth()->user()->galleries as $hasil_file )
                <h3>Tempat: {{ $hasil_file->path }}</h3>
                <form action="/open_file/{{ $hasil_file->id }}">
                    <button>{{ $hasil_file->nama_tampilan }}</button>

                </form>
                <p>Ukuran: {{ $hasil_file->ukuran_format }}</p>
                <p>Tanggal Upload: {{ $hasil_file->created_at->format('d-m-Y') }}</p>

                <form action="/rename_file/{{ $hasil_file->id }}">
                    <button>Rename</button>
                </form>

                <form action="/izin_file/{{ $hasil_file->id }}">
            
                    <button>Perizinan File</button>
                </form>

                <form action="/hapus/{{ $hasil_file->id }}" method="GET">
                    @csrf
               
                    <button>Hapus</button>

                </form>

                <a href='/download/{{ $hasil_file->id }}' method="GET">
                    <button>Download</button>

                </a>


    
            @endforeach
        
        
       
    
    @if (session('hapus_sukses'))
        <p>{{ session('hapus_sukses') }}</p>
    
    @endif


    <form action="/folder" method="POST">
        @csrf
        <input placeholder="Nama Folder" name="nama" type="text">
        <input name="parent_id" value="" type="hidden">
        <button>Buat folder baru</button>
    </form>


    @if (session('notif'))
        <p>{{ session('notif') }}</p>
    @endif

    @foreach (auth()->user()->folders->where('parent_id', operator: null) as $newFolder )
    <h3>Tempat: {{ $newFolder->path }}</h3>
        <form action="/folder_open/{{ $newFolder->id}}">
            <button>{{ $newFolder->nama_folder }}</button>
            

        </form>

        <p> Tanggal Upload: {{ $newFolder->created_at->format('d-m-Y') }}</p>

        <form action="/masuk_izin/{{ $newFolder->id }}">
            <button>Perizinan Folder</button>
        </form>

        <form action="/rename_folder/{{ $newFolder->id }}">
            <button>Rename Folder</button>
        </form>

        <form action="/hapus_folder/{{ $newFolder->id }}" method="GET">
            @csrf
            <button>Hapus</button>
        </form>

    
    @endforeach

    @if (session('folder_status'))
        <p>{{ session('folder_status') }}</p>
    
    @endif

    




    
</body>
</html>