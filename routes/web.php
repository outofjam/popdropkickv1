<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

// In routes/web.php
Route::get('/debug-session', function() {
    session()->put('test_key', 'test_value');
    session()->save();

    return [
        'session_id' => session()->getId(),
        'session_driver' => config('session.driver'),
        'test_value' => session()->get('test_value'),
        'session_working' => session()->get('test_key') === 'test_value',
        'database_sessions_table_exists' => \Schema::hasTable('sessions'),
    ];
});
