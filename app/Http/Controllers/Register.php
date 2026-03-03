<?php

namespace App\Http\Controllers;

use Hash;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;


class Register extends Controller
{
    public function tampil()
    {
        $pesan = 'Selamat Datang di Test Backend';
        return view('register', compact('pesan'));
    }
    public function register(Request $register)
    {
        $register->validate([
            'nama' => 'required|min:3|unique:users,name',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed'

        ]);

        $nama_user = $register->nama;
        $email_user = $register->email;
        $password_user = $register->password;

        User:: create([
            'name' => $nama_user,
            'email' => $email_user,
            'password' => Hash::make($password_user)
        ]);

        return redirect('register')->with('status','Register berhasil!!');


    }
    //
}
