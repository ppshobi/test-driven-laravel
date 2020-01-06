<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login()
    {
        if(! Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            return redirect('login')->withErrors([
                'email' => 'Credentials Do not match',
            ])->withInput(request()->except('password'));
        }
        return redirect('/backstage/concerts/new');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
