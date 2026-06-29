<?php

namespace App\Http\Controllers\Teacher;
use App\Http\Controllers\Controller;

use App\Models\ClassEnrollment;
use App\Models\ClassMaterial;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassMaterialController extends Controller
{
    public function index(ClassRoom $class)
    {
        $user = Auth::user();
        $this->authorizeMember($class);
        $class->load('materials.teacher');
        $view = $user->role === 'teacher' ? 'teacher.classes.materials' : 'student.classes.materials';
        return view($view, compact('class'));
    }

    public function store(Request $request, ClassRoom $class)
    {
        $this->authorizeTeacher($class);
        $request->validate([
            'title' => 'required|string|max:200',
            'file'  => 'required|file|max:51200',
        ]);

        $file = $request->file('file');

        ClassMaterial::create([
            'class_id'          => $class->class_id,
            'teacher_id'        => Auth::id(),
            'title'             => $request->title,
            'original_filename' => $file->getClientOriginalName(),
            'file_data'         => 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath())),
            'mime_type'         => $file->getClientMimeType(),
            'size_bytes'        => $file->getSize(),
        ]);

        return back()->with('success', 'File uploaded.');
    }

    /**
     * Stream file inline for in-browser preview from DB base64.
     */
    public function preview(ClassRoom $class, ClassMaterial $material)
    {
        $this->authorizeMember($class);

        if ((int) $material->class_id !== (int) $class->class_id) abort(404);
        if (!$material->file_data) abort(404, 'File not found.');

        // Strip the data URI prefix and decode
        $dataUri  = $material->file_data;
        $base64   = preg_replace('/^data:[^;]+;base64,/', '', $dataUri);
        $binary   = base64_decode($base64);
        $mime     = $material->mime_type ?? 'application/octet-stream';

        return response($binary, 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $material->original_filename . '"',
            'Content-Length'      => strlen($binary),
            'Cache-Control'       => 'private, max-age=3600',
        ]);
    }

    public function download(ClassRoom $class, ClassMaterial $material)
    {
        $this->authorizeMember($class);

        if ((int) $material->class_id !== (int) $class->class_id) abort(404);
        if (!$material->file_data) abort(404, 'File not found.');

        $base64 = preg_replace('/^data:[^;]+;base64,/', '', $material->file_data);
        $binary = base64_decode($base64);
        $mime   = $material->mime_type ?? 'application/octet-stream';

        return response($binary, 200, [
            'Content-Type'              => $mime,
            'Content-Disposition'       => 'attachment; filename="' . $material->original_filename . '"',
            'Content-Length'            => strlen($binary),
        ]);
    }

    public function destroy(ClassRoom $class, ClassMaterial $material)
    {
        $this->authorizeTeacher($class);

        if ((int) $material->class_id !== (int) $class->class_id) abort(404);

        $material->delete();

        return back()->with('success', 'File removed.');
    }

    private function authorizeTeacher(ClassRoom $class): void
    {
        if ((int) $class->teacher_id !== (int) Auth::id()) abort(403);
    }

    private function authorizeMember(ClassRoom $class): void
    {
        $user = Auth::user();
        if ((int) $class->teacher_id === (int) $user->user_id) return;

        $isMember = ClassEnrollment::where('class_id', $class->class_id)
            ->where('student_id', $user->user_id)
            ->where('status', 'active')
            ->exists();

        if (!$isMember) abort(403);
    }
}
