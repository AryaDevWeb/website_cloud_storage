<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @if (isset($isi_folder))

        <title>{{ $isi_folder->nama_folder }}</title>
    
    @endif
    
 
</head>
<body>

    <form action="/beranda/{{ auth()->id() }}">
        <button>Beranda</button>
    </form>

    
    @if (isset($berubah))
        <p>{{ $berubah }}</p>
    
    @endif

 

    @if (session('error'))
        <p>{{ session('error') }}</p>
    
    @endif

    @if (isset($isi_folder))
        <form action="/folder" method="POST">
        @csrf
        <input placeholder="Nama Folder" name="nama" type="text">
        <input value="{{ $isi_folder->id }}" name="parent_id" type="hidden">
        <button>Create Folder</button>

    </form>
    
    @endif

  

    @if (isset($isi_folder))

        <form action="/upload_subfolder" enctype="multipart/form-data" method="POST">
        @csrf
        <input name="upload" type="file">
        <input name="folder_id" value="{{ $isi_folder->id }}" type="hidden">
        <button>Upload File</button>
    </form>
    
    @endif
  

    @if (session('nama_tampil'))
        <button>{{ session('nama_tampil') }}</button>
    
    @endif

    @if (isset($status))
        <p>{{ $status }}</p>
    
    @endif


    @if (isset($isi_folder))
         @foreach ($isi_folder->files as $isi_file )
        <button>{{ $isi_file->file }}</button>

        <p>Ukuran: {{ $isi_file->ukuran_format }}</p>
        <p>Tanggal Upload: {{ $isi_file->created_at->format('d-m-Y') }}</p>

        <form action="/hapus_file/{{ $isi_file->id }}" method="GET">
            <button>Hapus</button>
        </form>

        <form action="/download_subfile/{{ $isi_file->id }}">
            <button>Download</button>
        </form>

        <form action="/rename_subfile/{{ $isi_file->id }}">
            <button>Rename</button>
        </form>


        <form action="/perizinan_subfile/{{ $isi_file->id }}">
            <button>Perizinan File</button>
        </form>


    @endforeach
    
    @endif


    @if (isset($isi_folder))
        @foreach ($isi_folder->children as $subfolder )
        <form action="/folder_open/{{ $subfolder->id }}">
            <button>{{ $subfolder->nama_folder }}</button>

        </form>

        <p> Tanggal Upload: {{ $subfolder->created_at->format('d-m-Y') }}</p>

        <form action="/hapus_subfolder/{{ $subfolder->id }}" method="GET">
            <button>Hapus</button>
        </form>

        <form action="/pindah_perizinan/{{ $subfolder->id }}">
            <button>Perizinan Folder</button>
        </form>

        <form action="/rename_subfolder/{{ $subfolder->id }}">
            <button>Rename Folder</button>
        </form>
   
    
    @endforeach
    
    @endif
    

    @if (session('notif'))
        <p>{{ session('notif') }}</p>
    
    @endif

    @if (session('status_subfolder'))
        <p>{{ session('status_subfolder') }}</p>
    
    @endif

    @if (session('status_file'))
        <p>{{ session('status_file') }}</p>
    
    @endif

    
</body>
</html>