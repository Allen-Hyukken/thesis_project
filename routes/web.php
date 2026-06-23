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
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\ActivitySubmissionController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\ExamAttemptController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AiTutorController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\AnalyticsController;

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

    // Members — its own page now
    Route::get('/classes/{class}/members', [ClassController::class, 'members'])->name('classes.members.index');
    Route::post('/classes/{class}/members/{student}/kick', [ClassController::class, 'kickMember'])->name('classes.members.kick');

    // Posting courses into a class (many-to-many)
    Route::post('/classes/{class}/courses', [ClassController::class, 'postCourse'])->name('classes.courses.post');
    Route::delete('/classes/{class}/courses/{course}', [ClassController::class, 'unpostCourse'])->name('classes.courses.unpost');

    // Files — its own page now
    Route::get('/classes/{class}/materials', [ClassMaterialController::class, 'index'])->name('classes.materials.index');
    Route::post('/classes/{class}/materials', [ClassMaterialController::class, 'store'])->name('classes.materials.store');
    Route::get('/classes/{class}/materials/{material}/download', [ClassMaterialController::class, 'download'])->name('classes.materials.download');
    Route::delete('/classes/{class}/materials/{material}', [ClassMaterialController::class, 'destroy'])->name('classes.materials.destroy');

    // Gradebook — its own page now
    Route::get('/classes/{class}/gradebook', [GradebookController::class, 'show'])->name('classes.gradebook');
    Route::post('/gradebook/activities/{submission}/grade', [GradebookController::class, 'gradeActivity'])->name('gradebook.activities.grade');
    Route::post('/gradebook/quiz-answers/{answer}/grade', [GradebookController::class, 'gradeQuizAnswer'])->name('gradebook.quiz-answers.grade');
    Route::post('/gradebook/exam-answers/{answer}/grade', [GradebookController::class, 'gradeExamAnswer'])->name('gradebook.exam-answers.grade');

    Route::get('/courses', [CourseController::class, 'index'])->name('courses');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::post('/courses/{course}/publish', [CourseController::class, 'publish'])->name('courses.publish');

    // Course outline (title / description / objectives)
    Route::put('/courses/{course}/outline', [CourseController::class, 'updateOutline'])->name('courses.outline.update');

    // Lessons/Topics and Activities (same table, item_type tells them apart)
    Route::post('/courses/{course}/modules', [ModuleController::class, 'store'])->name('courses.modules.store');
    Route::put('/courses/{course}/modules/{module}', [ModuleController::class, 'update'])->name('courses.modules.update');
    Route::delete('/courses/{course}/modules/{module}', [ModuleController::class, 'destroy'])->name('courses.modules.destroy');

    // Quizzes
    Route::post('/courses/{course}/quizzes', [QuizController::class, 'store'])->name('courses.quizzes.store');
    Route::post('/courses/{course}/quizzes/{quiz}/publish', [QuizController::class, 'publish'])->name('courses.quizzes.publish');
    Route::delete('/courses/{course}/quizzes/{quiz}', [QuizController::class, 'destroy'])->name('courses.quizzes.destroy');

    // Exams (kept separate from quizzes)
    Route::post('/courses/{course}/exams', [ExamController::class, 'store'])->name('courses.exams.store');
    Route::post('/courses/{course}/exams/{exam}/publish', [ExamController::class, 'publish'])->name('courses.exams.publish');
    Route::delete('/courses/{course}/exams/{exam}', [ExamController::class, 'destroy'])->name('courses.exams.destroy');

    // AI draft generation (read-only — saving happens through the routes above)
    Route::post('/courses/ai/outline', [CourseAiController::class, 'outline'])->name('courses.ai.outline');
    Route::post('/courses/{course}/ai/lesson-content', [CourseAiController::class, 'lessonContent'])->name('courses.ai.lesson-content');
    Route::post('/courses/{course}/ai/activity', [CourseAiController::class, 'activity'])->name('courses.ai.activity');
    Route::post('/courses/{course}/ai/assessment', [CourseAiController::class, 'assessment'])->name('courses.ai.assessment');

    Route::get('/learning-materials', function () { return "Materials Page"; })->name('materials');
    Route::get('/students', function () { return "Students List Page"; })->name('students');
    Route::get('/ai-generator', fn () => redirect()->route('teacher.courses.create'))->name('ai-generate');
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('results');
});


/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'student', 'as' => 'student.'], function () {

    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

    Route::get('/classes', [ClassController::class, 'index'])->name('classes');
    Route::post('/classes/join', [ClassController::class, 'join'])->name('classes.join');
    Route::get('/classes/{class}', [ClassController::class, 'show'])->name('classes.show');

    // Members — its own page now (read-only roster)
    Route::get('/classes/{class}/members', [ClassController::class, 'members'])->name('classes.members.index');

    // Files — its own page now (download only)
    Route::get('/classes/{class}/materials', [ClassMaterialController::class, 'index'])->name('classes.materials.index');
    Route::get('/classes/{class}/materials/{material}/download', [ClassMaterialController::class, 'download'])->name('classes.materials.download');

    // My Scores — its own page now
    Route::get('/classes/{class}/scores', [GradebookController::class, 'myScores'])->name('classes.scores');

    // Taking a posted course
    Route::get('/classes/{class}/courses/{course}', [StudentCourseController::class, 'show'])->name('classes.courses.show');

    Route::post('/activities/{module}/submit', [ActivitySubmissionController::class, 'store'])->name('activities.submit');

    Route::get('/quizzes/{quiz}/take', [QuizAttemptController::class, 'show'])->name('quizzes.take');
    Route::post('/quizzes/{quiz}/submit', [QuizAttemptController::class, 'submit'])->name('quizzes.submit');

    Route::get('/exams/{exam}/take', [ExamAttemptController::class, 'show'])->name('exams.take');
    Route::post('/exams/{exam}/submit', [ExamAttemptController::class, 'submit'])->name('exams.submit');

    // AI Study Assistant (FR.1.5.1–1.5.2)
    Route::get('/ai-tutor', [AiTutorController::class, 'pickCourse'])->name('ai-tutor');
    Route::get('/classes/{class}/courses/{course}/ai-tutor', [AiTutorController::class, 'show'])->name('classes.courses.ai-tutor');
    Route::post('/classes/{class}/courses/{course}/ai-tutor/ask', [AiTutorController::class, 'ask'])->name('classes.courses.ai-tutor.ask');

    // AI Flashcards (FR.1.5.3–1.5.4)
    Route::get('/flashcards', [FlashcardController::class, 'pickCourse'])->name('flashcards');
    Route::get('/classes/{class}/courses/{course}/flashcards', [FlashcardController::class, 'index'])->name('classes.courses.flashcards');
    Route::post('/classes/{class}/courses/{course}/flashcards/generate', [FlashcardController::class, 'generate'])->name('classes.courses.flashcards.generate');

    Route::get('/my-courses', function () { return "Student Courses"; })->name('courses');
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
    Route::get('/profile/{user}', [ProfileController::class, 'showUser'])->name('profile.view');
});
