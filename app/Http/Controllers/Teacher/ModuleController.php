<?php

namespace App\Http\Controllers\Teacher;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Concerns\AuthorizesCourseOwner;
use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    use AuthorizesCourseOwner;

    /**
     * Add a new Lesson/Topic OR a new Activity (item_type tells us which).
     * Works the same whether the fields were typed by hand or pre-filled
     * by the "Generate with AI" button — by the time this runs it's just
     * form data.
     */
    public function store(Request $request, Course $course)
    {
        $this->authorizeOwner($course);

        $validated = $request->validate([
            'item_type'     => 'required|in:lesson,activity',
            'title'         => 'required|string|max:200',
            'content'       => 'required|string',
            'activity_type' => 'required_if:item_type,activity|nullable|in:assignment,discussion,project,reflection',
            'points'        => 'nullable|integer|min:1|max:100',
            'due_at'        => 'nullable|date',
            'ai_generated'  => 'nullable|boolean',
        ]);

        $nextOrder = $course->modules()
                ->where('item_type', $validated['item_type'])
                ->max('order_index') + 1;

        $course->modules()->create([
            'item_type'     => $validated['item_type'],
            'title'         => $validated['title'],
            'content'       => $validated['content'],
            'activity_type' => $validated['item_type'] === 'activity' ? $validated['activity_type'] : null,
            'points'        => $validated['item_type'] === 'activity' ? ($validated['points'] ?? 10) : null,
            'due_at'        => $validated['item_type'] === 'activity' ? ($validated['due_at'] ?? null) : null,
            'order_index'   => $nextOrder,
            'ai_generated'  => $request->boolean('ai_generated'),
        ]);

        $label = $validated['item_type'] === 'activity' ? 'Activity' : 'Lesson/topic';

        return back()->with('success', "{$label} added.");
    }

    /**
     * Used to save edited AI-generated content into a topic that already
     * exists (e.g. the teacher added the topic title during outline review,
     * then generated + edited the full lesson content afterward).
     */
    public function update(Request $request, Course $course, CourseModule $module)
    {
        $this->authorizeOwner($course);

        if ((int) $module->course_id !== (int) $course->course_id) {
            abort(404);
        }

        $validated = $request->validate([
            'title'         => 'required|string|max:200',
            'content'       => 'required|string',
            'activity_type' => 'nullable|in:assignment,discussion,project,reflection',
            'points'        => 'nullable|integer|min:1|max:100',
            'due_at'        => 'nullable|date',
            'ai_generated'  => 'nullable|boolean',
        ]);

        $module->update([
            'title'         => $validated['title'],
            'content'       => $validated['content'],
            'activity_type' => $module->isActivity() ? ($validated['activity_type'] ?? $module->activity_type) : $module->activity_type,
            'points'        => $module->isActivity() ? ($validated['points'] ?? $module->points) : $module->points,
            'due_at'        => $module->isActivity() ? ($validated['due_at'] ?? $module->due_at) : $module->due_at,
            'ai_generated'  => $request->boolean('ai_generated') || $module->ai_generated,
        ]);

        return back()->with('success', ($module->isActivity() ? 'Activity' : 'Lesson/topic') . ' updated.');
    }

    public function destroy(Course $course, CourseModule $module)
    {
        $this->authorizeOwner($course);

        if ((int) $module->course_id !== (int) $course->course_id) {
            abort(404);
        }

        $label = $module->isActivity() ? 'Activity' : 'Lesson/topic';
        $module->delete();

        return back()->with('success', "{$label} deleted.");
    }

}
