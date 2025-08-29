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

Route::get('/session-debug', function () {
    return [
        'cookie_name_expected' => config('session.cookie'),
        'cookies_received'     => request()->cookies->all(),
        'session_id'           => session()->getId(),
        'is_secure_request'    => request()->isSecure(),
        'scheme'               => request()->getScheme(),
    ];
});


