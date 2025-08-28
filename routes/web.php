<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});
Route::get('/session-test', function () {
    Auth::attempt(['email'=>'idawoodjee@mac.com','password'=>'manman']);
    return [
        'auth_check' => Auth::check(),
        'session_id' => session()->getId(),
        'session_cookie' => request()->cookie(config('session.cookie')),
    ];
});

