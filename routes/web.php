<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('auth-login');
});

Route::get('/login', function () {
    return view('auth-login');
})->name('login');

Route::get('/register', function () {
    return view('auth-register');
})->name('register');

Route::get('/forgot-password', function () {
    return view('auth-forgot-password');
})->name('password.request');

Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/login',    [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');


/*
|--------------------------------------------------------------------------
| Teacher Routes
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'teacher', 'as' => 'teacher.'], function () {

    Route::get('/dashboard', function () {
        return view('teacher.dashboard');
    })->name('dashboard');

    Route::get('/classes', function () { return "Teacher Classes Page"; })->name('classes');
    Route::get('/courses', function () { return "Teacher Courses Page"; })->name('courses');
    Route::get('/learning-materials', function () { return "Materials Page"; })->name('materials');
    Route::get('/students', function () { return "Students List Page"; })->name('students');
    Route::get('/ai-generator', function () { return "AI Course Builder Page"; })->name('ai-generate');
    Route::get('/analytics', function () { return "Results Page"; })->name('results');
    Route::get('/profile', function () { return "Teacher Profile Page"; })->name('profile');
});


/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'student', 'as' => 'student.'], function () {

    Route::get('/dashboard', function () {
        return view('student.dashboard');
    })->name('dashboard');

    Route::get('/my-courses', function () { return "Student Courses"; })->name('courses');
    Route::get('/ai-tutor', function () { return "AI Chat Assistant"; })->name('ai-tutor');
    Route::get('/flashcards', function () { return "AI Generated Flashcards"; })->name('flashcards');
});
