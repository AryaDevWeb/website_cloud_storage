<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class Login extends Controller
{
    public function login(Request $login)
    {
        $contoh = $login->validate([
            'username' => 'required|string',
            'password' => 'required'
        ]);

        $ingat = $login->has('ingat');


        if (Auth::attempt($contoh,$ingat)) {
            $login->session()->regenerate();


            return redirect()->intended('/beranda/' . auth()->id())->with('status','Login Berhasil');
        }else {
            return redirect('login')->with('error','Username atau password salah!!');
        }
    


        
    }
    //
}
