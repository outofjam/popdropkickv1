<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

// In routes/web.php
Route::get('/set-session', function() {
    session()->put('persistent_test', 'hello_world');
    session()->save();
    return [
        'message' => 'Session set',
        'session_id' => session()->getId(),
        'immediate_read' => session()->get('persistent_test')
    ];
});

Route::get('/get-session', function() {
    return [
        'session_id' => session()->getId(),
        'persistent_test' => session()->get('persistent_test'),
        'all_session' => session()->all()
    ];
});

Route::get('/url-debug', function() {
    return [
        'config_app_url' => config('app.url'),
        'config_app_env' => config('app.env'),
        'url_current' => url()->current(),
        'url_previous' => url()->previous(),
        'request_scheme' => request()->getScheme(),
        'request_is_secure' => request()->isSecure(),
        'app_debug' => config('app.debug'),
    ];
});

Route::get('/force-login', function() {
    $user = \App\Models\User::where('email', 'test@example.com')->first();
    if ($user) {
        Auth::login($user);
        return redirect('/admin');
    }
    return 'User not found';
});
