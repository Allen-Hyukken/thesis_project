<?php

namespace App\Http\Controllers\Concerns;

trait HandlesAssessmentAttempts
{
    /**
     * $submission is a QuizSubmission or ExamSubmission (both expose an
     * answers() hasMany). $questions must have 'choices' eager loaded for
     * multiple_choice grading to avoid N+1 queries.
     *
     * $responses is keyed by question_id: either the chosen choice_id
     * (multiple_choice) or raw text (open_ended).
     *
     * Returns [score, maxScore]. Open-ended questions contribute 0 to the
     * running score until a teacher grades them — the submission's true
     * final score may be higher once review is complete.
     */
    protected function recordAnswers($submission, $questions, array $responses): array
    {
        $score = 0;
        $maxScore = 0;

        foreach ($questions as $question) {
            $maxScore += $question->points;
            $response = $responses[$question->question_id] ?? null;

            if ($question->question_type === 'multiple_choice') {
                $choice = $question->choices->firstWhere('choice_id', (int) $response);
                $isCorrect = $choice ? (bool) $choice->is_correct : false;
                $pointsEarned = $isCorrect ? $question->points : 0;

                $submission->answers()->create([
                    'question_id'   => $question->question_id,
                    'answer_text'   => $choice->choice_text ?? null,
                    'is_correct'    => $isCorrect,
                    'points_earned' => $pointsEarned,
                ]);

                $score += $pointsEarned;
            } else {
                $submission->answers()->create([
                    'question_id'   => $question->question_id,
                    'answer_text'   => is_string($response) ? $response : null,
                    'is_correct'    => null,
                    'points_earned' => null, // pending manual review
                ]);
            }
        }

        return [$score, $maxScore];
    }
}
