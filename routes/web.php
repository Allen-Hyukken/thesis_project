<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseAiController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ProfileController;
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

    Route::get('/classes', [ClassController::class, 'index'])->name('classes');
    Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');

    Route::get('/courses', [CourseController::class, 'index'])->name('courses');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::post('/courses/{course}/publish', [CourseController::class, 'publish'])->name('courses.publish');

    // Lessons/Topics and Activities (same table, item_type tells them apart)
    Route::post('/courses/{course}/modules', [ModuleController::class, 'store'])->name('courses.modules.store');
    Route::put('/courses/{course}/modules/{module}', [ModuleController::class, 'update'])->name('courses.modules.update');

    // Quizzes
    Route::post('/courses/{course}/quizzes', [QuizController::class, 'store'])->name('courses.quizzes.store');
    Route::post('/courses/{course}/quizzes/{quiz}/publish', [QuizController::class, 'publish'])->name('courses.quizzes.publish');

    // Exams (kept separate from quizzes)
    Route::post('/courses/{course}/exams', [ExamController::class, 'store'])->name('courses.exams.store');
    Route::post('/courses/{course}/exams/{exam}/publish', [ExamController::class, 'publish'])->name('courses.exams.publish');

    // AI draft generation (read-only — saving happens through the routes above)
    Route::post('/courses/ai/outline', [CourseAiController::class, 'outline'])->name('courses.ai.outline');
    Route::post('/courses/{course}/ai/lesson-content', [CourseAiController::class, 'lessonContent'])->name('courses.ai.lesson-content');
    Route::post('/courses/{course}/ai/activity', [CourseAiController::class, 'activity'])->name('courses.ai.activity');
    Route::post('/courses/{course}/ai/assessment', [CourseAiController::class, 'assessment'])->name('courses.ai.assessment');

    Route::get('/learning-materials', function () { return "Materials Page"; })->name('materials');
    Route::get('/students', function () { return "Students List Page"; })->name('students');
    Route::get('/ai-generator', function () { return "AI Course Builder Page"; })->name('ai-generate');
    Route::get('/analytics', function () { return "Results Page"; })->name('results');

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

    Route::get('/classes', [ClassController::class, 'index'])->name('classes');
    Route::post('/classes/join', [ClassController::class, 'join'])->name('classes.join');

    Route::get('/my-courses', function () { return "Student Courses"; })->name('courses');
    Route::get('/ai-tutor', function () { return "AI Chat Assistant"; })->name('ai-tutor');
    Route::get('/flashcards', function () { return "AI Generated Flashcards"; })->name('flashcards');
});
/*
|--------------------------------------------------------------------------
| Profile Routes (shared — works for both students and teachers)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
