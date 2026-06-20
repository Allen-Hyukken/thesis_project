<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\ClassMaterialController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseAiController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\GradebookController;
use App\Http\Controllers\StudentCourseController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\ActivitySubmissionController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\ExamAttemptController;
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

    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');

    Route::get('/classes', [ClassController::class, 'index'])->name('classes');
    Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');
    Route::get('/classes/{class}', [ClassController::class, 'show'])->name('classes.show');

    // Posting courses into a class (many-to-many)
    Route::post('/classes/{class}/courses', [ClassController::class, 'postCourse'])->name('classes.courses.post');
    Route::delete('/classes/{class}/courses/{course}', [ClassController::class, 'unpostCourse'])->name('classes.courses.unpost');

    // Members drawer — kicking
    Route::post('/classes/{class}/members/{student}/kick', [ClassController::class, 'kickMember'])->name('classes.members.kick');

    // Files drawer
    Route::post('/classes/{class}/materials', [ClassMaterialController::class, 'store'])->name('classes.materials.store');
    Route::get('/classes/{class}/materials/{material}/download', [ClassMaterialController::class, 'download'])->name('classes.materials.download');
    Route::delete('/classes/{class}/materials/{material}', [ClassMaterialController::class, 'destroy'])->name('classes.materials.destroy');

    // Gradebook drawer — grading actions
    Route::post('/gradebook/activities/{submission}/grade', [GradebookController::class, 'gradeActivity'])->name('gradebook.activities.grade');
    Route::post('/gradebook/quiz-answers/{answer}/grade', [GradebookController::class, 'gradeQuizAnswer'])->name('gradebook.quiz-answers.grade');
    Route::post('/gradebook/exam-answers/{answer}/grade', [GradebookController::class, 'gradeExamAnswer'])->name('gradebook.exam-answers.grade');

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
    Route::get('/ai-generator', fn () => redirect()->route('teacher.courses.create'))->name('ai-generate');
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
    Route::get('/classes/{class}', [ClassController::class, 'show'])->name('classes.show');

    // Files drawer (shared download logic, same controller as teacher side)
    Route::get('/classes/{class}/materials/{material}/download', [ClassMaterialController::class, 'download'])->name('classes.materials.download');

    // Taking a posted course
    Route::get('/classes/{class}/courses/{course}', [StudentCourseController::class, 'show'])->name('classes.courses.show');

    // Activities — text/file submission
    Route::post('/activities/{module}/submit', [ActivitySubmissionController::class, 'store'])->name('activities.submit');

    // Quizzes — one attempt
    Route::get('/quizzes/{quiz}/take', [QuizAttemptController::class, 'show'])->name('quizzes.take');
    Route::post('/quizzes/{quiz}/submit', [QuizAttemptController::class, 'submit'])->name('quizzes.submit');

    // Exams — one attempt, kept separate from quizzes
    Route::get('/exams/{exam}/take', [ExamAttemptController::class, 'show'])->name('exams.take');
    Route::post('/exams/{exam}/submit', [ExamAttemptController::class, 'submit'])->name('exams.submit');

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
