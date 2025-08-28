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
