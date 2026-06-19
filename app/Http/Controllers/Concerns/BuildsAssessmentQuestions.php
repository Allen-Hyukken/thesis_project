<?php

namespace App\Http\Controllers\Concerns;

trait BuildsAssessmentQuestions
{
    /**
     * $assessment is either a Quiz or an Exam — both expose a
     * questions() hasMany relation, and their question/choice
     * models share the same shape, so this logic works for either.
     *
     * Expected shape per question:
     * [
     *   'question_text' => string,
     *   'question_type' => 'multiple_choice'|'open_ended',
     *   'points'         => int,
     *   'choices'        => [['text' => string], ...]  // up to 4, for multiple_choice
     *   'correct_choice' => int|null  // index into choices that is correct
     *   'correct_answer' => string|null  // model answer, for open_ended
     * ]
     */
    protected function saveAssessmentQuestions($assessment, array $questions): void
    {
        $labels = ['A', 'B', 'C', 'D'];

        foreach ($questions as $i => $q) {
            $isMultipleChoice = ($q['question_type'] ?? 'multiple_choice') === 'multiple_choice';

            $question = $assessment->questions()->create([
                'question_text'  => $q['question_text'] ?? '',
                'question_type'  => $isMultipleChoice ? 'multiple_choice' : 'open_ended',
                'correct_answer' => $isMultipleChoice ? null : ($q['correct_answer'] ?? null),
                'points'         => $q['points'] ?? 1,
                'order_index'    => $i,
            ]);

            if (! $isMultipleChoice) {
                continue;
            }

            $correctIndex = isset($q['correct_choice']) ? (int) $q['correct_choice'] : null;

            foreach (($q['choices'] ?? []) as $j => $choice) {
                $text = is_array($choice) ? ($choice['text'] ?? '') : $choice;

                if ($text === '') {
                    continue;
                }

                $question->choices()->create([
                    'choice_label' => $labels[$j] ?? chr(65 + $j),
                    'choice_text'  => $text,
                    'is_correct'   => $correctIndex === $j,
                ]);
            }
        }
    }
}
