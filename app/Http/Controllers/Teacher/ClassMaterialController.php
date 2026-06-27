<?php

namespace App\Http\Controllers\Teacher;
use App\Http\Controllers\Controller;

use App\Models\ClassEnrollment;
use App\Models\ClassMaterial;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClassMaterialController extends Controller
{
    /**
     * Files page — its own page now instead of a drawer. Teacher view
     * includes the upload form and delete buttons; student view is
     * read-only (download links only).
     */
    public function index(ClassRoom $class)
    {
        $user = Auth::user();
        $this->authorizeMember($class);

        $class->load('materials.teacher');

        $view = $user->role === 'teacher' ? 'teacher.classes.materials' : 'student.classes.materials';

        return view($view, compact('class'));
    }

    /**
     * Teacher: upload a file of any type to this class.
     */
    public function store(Request $request, ClassRoom $class)
    {
        $this->authorizeTeacher($class);

        $request->validate([
            'title' => 'required|string|max:200',
            'file'  => 'required|file|max:51200', // 50MB
        ]);

        $file = $request->file('file');
        $storedPath = $file->store("class-materials/{$class->class_id}", 'local');

        ClassMaterial::create([
            'class_id'          => $class->class_id,
            'teacher_id'        => Auth::id(),
            'title'             => $request->title,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path'       => $storedPath,
            'mime_type'         => $file->getClientMimeType(),
            'size_bytes'        => $file->getSize(),
        ]);

        return back()->with('success', 'File uploaded.');
    }

    /**
     * Any active member of the class (teacher or student) can download.
     */
    public function download(ClassRoom $class, ClassMaterial $material)
    {
        $this->authorizeMember($class);

        if ((int) $material->class_id !== (int) $class->class_id) {
            abort(404);
        }

        if (! Storage::disk('local')->exists($material->stored_path)) {
            abort(404, 'File no longer exists.');
        }

        return Storage::disk('local')->download($material->stored_path, $material->original_filename);
    }

    /**
     * Teacher only: remove a file.
     */
    public function destroy(ClassRoom $class, ClassMaterial $material)
    {
        $this->authorizeTeacher($class);

        if ((int) $material->class_id !== (int) $class->class_id) {
            abort(404);
        }

        Storage::disk('local')->delete($material->stored_path);
        $material->delete();

        return back()->with('success', 'File removed.');
    }

    private function authorizeTeacher(ClassRoom $class): void
    {
        if ((int) $class->teacher_id !== (int) Auth::id()) {
            abort(403);
        }
    }

    private function authorizeMember(ClassRoom $class): void
    {
        $user = Auth::user();

        if ((int) $class->teacher_id === (int) $user->user_id) {
            return;
        }

        $isMember = ClassEnrollment::where('class_id', $class->class_id)
            ->where('student_id', $user->user_id)
            ->where('status', 'active')
            ->exists();

        if (! $isMember) {
            abort(403);
        }
    }
}
