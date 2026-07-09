<?php

use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Auth\AuthController;

// Shared
use App\Http\Controllers\ClassController;
use App\Http\Controllers\ProfileController;

// Teacher
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Teacher\CourseController;
use App\Http\Controllers\Teacher\CourseAiController;
use App\Http\Controllers\Teacher\ModuleController;
use App\Http\Controllers\Teacher\QuizController;
use App\Http\Controllers\Teacher\ExamController;
use App\Http\Controllers\Teacher\GradebookController;
use App\Http\Controllers\Teacher\AnalyticsController;
use App\Http\Controllers\Teacher\ClassMaterialController;

// Student
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentCourseController;
use App\Http\Controllers\Student\QuizAttemptController;
use App\Http\Controllers\Student\ExamAttemptController;
use App\Http\Controllers\Student\ActivitySubmissionController;

// AI (EDITH)
use App\Http\Controllers\AI\AiTutorController;
use App\Http\Controllers\AI\FlashcardController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('auth.login'))->name('home');
Route::get('/login',           fn () => view('auth.login'))->name('login');
Route::get('/register',        fn () => view('auth.register'))->name('register');
Route::get('/forgot-password', fn () => view('auth.forgot-password'))->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

// ADD THESE TWO LINES HERE:
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password',        [AuthController::class, 'resetPassword'])->name('password.update');

Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/login',    [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');



/*
|--------------------------------------------------------------------------
| Teacher Routes
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'teacher', 'as' => 'teacher.', 'middleware' => ['auth', 'role:teacher']], function () {

    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');

    // Classes
    Route::get('/classes',                    [ClassController::class, 'index'])->name('classes');
    Route::post('/classes',                   [ClassController::class, 'store'])->name('classes.store');
    Route::get('/classes/{class}',            [ClassController::class, 'show'])->name('classes.show');

    // Archive / Restore
    Route::post('/classes/{class}/archive',   [ClassController::class, 'archive'])->name('classes.archive');
    Route::post('/classes/{class}/unarchive', [ClassController::class, 'unarchive'])->name('classes.unarchive');

    // Members
    Route::get('/classes/{class}/members',                          [ClassController::class, 'members'])->name('classes.members.index');
    Route::post('/classes/{class}/members/{student}/kick',          [ClassController::class, 'kickMember'])->name('classes.members.kick');

    // Course postings (many-to-many)
    Route::post('/classes/{class}/courses',                         [ClassController::class, 'postCourse'])->name('classes.courses.post');
    Route::delete('/classes/{class}/courses/{course}',              [ClassController::class, 'unpostCourse'])->name('classes.courses.unpost');

    // Materials
    Route::get('/classes/{class}/materials',                        [ClassMaterialController::class, 'index'])->name('classes.materials.index');
    Route::post('/classes/{class}/materials',                       [ClassMaterialController::class, 'store'])->name('classes.materials.store');
    Route::get('/classes/{class}/materials/{material}/preview',     [ClassMaterialController::class, 'preview'])->name('classes.materials.preview');
    Route::get('/classes/{class}/materials/{material}/download',    [ClassMaterialController::class, 'download'])->name('classes.materials.download');
    Route::delete('/classes/{class}/materials/{material}',          [ClassMaterialController::class, 'destroy'])->name('classes.materials.destroy');

    // Gradebook
    Route::get('/classes/{class}/gradebook',                        [GradebookController::class, 'show'])->name('classes.gradebook');
    Route::post('/gradebook/activities/{submission}/grade',         [GradebookController::class, 'gradeActivity'])->name('gradebook.activities.grade');
    Route::post('/gradebook/quiz-answers/{answer}/grade',           [GradebookController::class, 'gradeQuizAnswer'])->name('gradebook.quiz-answers.grade');
    Route::post('/gradebook/exam-answers/{answer}/grade',           [GradebookController::class, 'gradeExamAnswer'])->name('gradebook.exam-answers.grade');

    // Courses
    Route::get('/courses',                    [CourseController::class, 'index'])->name('courses');
    Route::get('/courses/create',             [CourseController::class, 'create'])->name('courses.create');
    Route::get('/courses/trash',              [CourseController::class, 'trash'])->name('courses.trash'); // <-- Moved above wildcards to resolve 404 conflict
    Route::post('/courses',                   [CourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{course}',           [CourseController::class, 'show'])->name('courses.show');
    Route::post('/courses/{course}/publish',  [CourseController::class, 'publish'])->name('courses.publish');
    Route::put('/courses/{course}/outline',   [CourseController::class, 'updateOutline'])->name('courses.outline.update');

    // Modules (lessons + activities)
    Route::post('/courses/{course}/modules',            [ModuleController::class, 'store'])->name('courses.modules.store');
    Route::put('/courses/{course}/modules/{module}',    [ModuleController::class, 'update'])->name('courses.modules.update');
    Route::delete('/courses/{course}/modules/{module}', [ModuleController::class, 'destroy'])->name('courses.modules.destroy');

    // Quizzes
    Route::post('/courses/{course}/quizzes',                        [QuizController::class, 'store'])->name('courses.quizzes.store');
    Route::put('/courses/{course}/quizzes/{quiz}',                  [QuizController::class, 'update'])->name('courses.quizzes.update');
    Route::post('/courses/{course}/quizzes/{quiz}/publish',         [QuizController::class, 'publish'])->name('courses.quizzes.publish');
    Route::delete('/courses/{course}/quizzes/{quiz}',               [QuizController::class, 'destroy'])->name('courses.quizzes.destroy');

    // Exams
    Route::post('/courses/{course}/exams',                          [ExamController::class, 'store'])->name('courses.exams.store');
    Route::put('/courses/{course}/exams/{exam}',                    [ExamController::class, 'update'])->name('courses.exams.update');
    Route::post('/courses/{course}/exams/{exam}/publish',           [ExamController::class, 'publish'])->name('courses.exams.publish');
    Route::delete('/courses/{course}/exams/{exam}',                 [ExamController::class, 'destroy'])->name('courses.exams.destroy');

    // EDITH AI generation (draft only — saving goes through the routes above)
    Route::post('/courses/ai/outline',                              [CourseAiController::class, 'outline'])->name('courses.ai.outline');
    Route::post('/courses/{course}/ai/lesson-content',             [CourseAiController::class, 'lessonContent'])->name('courses.ai.lesson-content');
    Route::post('/courses/{course}/ai/activity',                   [CourseAiController::class, 'activity'])->name('courses.ai.activity');
    Route::post('/courses/{course}/ai/assessment',                 [CourseAiController::class, 'assessment'])->name('courses.ai.assessment');

    // Course Trash & Soft Deletes
    Route::delete('/courses/{course}/trash', [CourseController::class, 'softDelete'])->name('courses.trash.move');
    Route::post('/courses/{id}/restore', [CourseController::class, 'restore'])->name('courses.restore');
    Route::delete('/courses/{id}/force-delete', [CourseController::class, 'forceDelete'])->name('courses.force-delete');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('results');

    // Stubs (not yet implemented)
    Route::get('/ai-generator', fn () => redirect()->route('teacher.courses.create'))->name('ai-generate');
});


/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'student', 'as' => 'student.', 'middleware' => ['auth', 'role:student']], function () {

    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

    // Classes
    Route::get('/classes',             [ClassController::class, 'index'])->name('classes');
    Route::post('/classes/join',       [ClassController::class, 'join'])->name('classes.join');
    Route::get('/classes/{class}',     [ClassController::class, 'show'])->name('classes.show');

    // Archive / Restore
    Route::post('/classes/{class}/archive',   [ClassController::class, 'studentArchive'])->name('classes.archive');
    Route::post('/classes/{class}/unarchive', [ClassController::class, 'studentUnarchive'])->name('classes.unarchive');

    // Members (read-only roster)
    Route::get('/classes/{class}/members',                          [ClassController::class, 'members'])->name('classes.members.index');

    // Materials (download only)
    Route::get('/classes/{class}/materials',                        [ClassMaterialController::class, 'index'])->name('classes.materials.index');
    Route::get('/classes/{class}/materials/{material}/preview',     [ClassMaterialController::class, 'preview'])->name('classes.materials.preview');
    Route::get('/classes/{class}/materials/{material}/download',    [ClassMaterialController::class, 'download'])->name('classes.materials.download');

    // My Scores
    Route::get('/classes/{class}/scores',                           [GradebookController::class, 'myScores'])->name('classes.scores');

    // Course viewer
    Route::get('/classes/{class}/courses/{course}',                 [StudentCourseController::class, 'show'])->name('classes.courses.show');

    // Activity submission
    Route::post('/activities/{module}/submit',                      [ActivitySubmissionController::class, 'store'])->name('activities.submit');

    // Quizzes
    Route::get('/quizzes/{quiz}/take',      [QuizAttemptController::class, 'show'])->name('quizzes.take');
    Route::post('/quizzes/{quiz}/submit',   [QuizAttemptController::class, 'submit'])->name('quizzes.submit');

    // Exams
    Route::get('/exams/{exam}/take',        [ExamAttemptController::class, 'show'])->name('exams.take');
    Route::post('/exams/{exam}/submit',     [ExamAttemptController::class, 'submit'])->name('exams.submit');

    // Activity submission file access
    Route::get('/activities/submissions/{submission}/preview',  [ActivitySubmissionController::class, 'previewFile'])->name('activities.submissions.preview');
    Route::get('/activities/submissions/{submission}/download', [ActivitySubmissionController::class, 'downloadFile'])->name('activities.submissions.download');

    // EDITH AI Tutor
    Route::get('/ai-tutor',                                                         [AiTutorController::class, 'pickCourse'])->name('ai-tutor');
    Route::get('/classes/{class}/courses/{course}/ai-tutor',                        [AiTutorController::class, 'show'])->name('classes.courses.ai-tutor');
    Route::post('/classes/{class}/courses/{course}/ai-tutor/ask',                   [AiTutorController::class, 'ask'])->name('classes.courses.ai-tutor.ask');

    // EDITH Flashcards
    Route::get('/flashcards',                                                        [FlashcardController::class, 'pickCourse'])->name('flashcards');
    Route::get('/classes/{class}/courses/{course}/flashcards',                       [FlashcardController::class, 'index'])->name('classes.courses.flashcards');
    Route::post('/classes/{class}/courses/{course}/flashcards/generate',             [FlashcardController::class, 'generate'])->name('classes.courses.flashcards.generate');
});


/*
|--------------------------------------------------------------------------
| Profile Routes  (shared — works for both teachers and students)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/profile',        [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit',   [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',        [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/{user}', [ProfileController::class, 'showUser'])->name('profile.view');
});
