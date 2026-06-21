<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GemmaService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = (string) config('gemma.api_key');
        $this->model   = (string) config('gemma.model');
        $this->baseUrl = (string) config('gemma.base_url');
    }

    public function generateOutline(string $topic, ?string $notes = null): array
    {
        $notes = $notes ?: 'None provided.';

        $prompt = <<<PROMPT
You are an instructional designer helping a college teacher draft a new course for a Philippine technological university.

Course topic: {$topic}
Teacher's notes: {$notes}

Design a complete course outline. Respond with ONLY valid JSON (no markdown fences, no commentary) in exactly this shape:

{
  "title": "string, a clear course title",
  "description": "string, 2-3 sentences describing the course",
  "learning_objectives": "string, 3-5 bullet points separated by newlines, each starting with a dash",
  "modules": [
    { "title": "string, lesson/topic title", "summary": "string, 1-2 sentence summary of what this topic covers" }
  ]
}

Include between 4 and 8 modules, ordered logically from foundational to advanced.
PROMPT;

        return $this->callJson($prompt);
    }

    public function generateLessonContent(string $courseTitle, string $moduleTitle, ?string $summary = null): array
    {
        $summary = $summary ?: 'No additional summary provided.';

        $prompt = <<<PROMPT
You are writing lesson content for a college course titled "{$courseTitle}".
Write the full lesson content for the topic: "{$moduleTitle}".
Context/summary for this topic: {$summary}

Respond with ONLY valid JSON (no markdown fences) in exactly this shape:
{ "content": "string, the full lesson content in plain text with clear paragraph breaks, ready for students to read directly" }

Aim for roughly 400-700 words: thorough enough for one class session, well-organized, free of filler.
PROMPT;

        return $this->callJson($prompt);
    }

    public function generateActivity(string $courseTitle, string $moduleTitle): array
    {
        $prompt = <<<PROMPT
You are designing a learning activity for the topic "{$moduleTitle}" in the course "{$courseTitle}".

Respond with ONLY valid JSON (no markdown fences) in exactly this shape:
{
  "title": "string, short activity title",
  "activity_type": "one of: assignment, discussion, project, reflection",
  "content": "string, clear step-by-step instructions for students",
  "points": number, a reasonable point value between 5 and 50
}
PROMPT;

        return $this->callJson($prompt);
    }

    public function generateAssessment(string $courseTitle, string $moduleTitle, string $kind = 'quiz', int $numQuestions = 5): array
    {
        $label = $kind === 'exam' ? 'exam' : 'short quiz';

        $prompt = <<<PROMPT
You are writing a {$label} for the topic "{$moduleTitle}" in the course "{$courseTitle}".

Create exactly {$numQuestions} questions, mostly multiple_choice with a few open_ended.

Respond with ONLY valid JSON (no markdown fences) in exactly this shape:
{
  "title": "string, a title for this {$label}",
  "questions": [
    {
      "question_text": "string",
      "question_type": "multiple_choice or open_ended",
      "points": number,
      "choices": [
        { "choice_label": "A", "choice_text": "string", "is_correct": true or false }
      ],
      "correct_answer": "string or null — a model answer for open_ended questions, null for multiple_choice"
    }
  ]
}

For multiple_choice questions, include exactly 4 choices labeled A, B, C, D with exactly one marked is_correct: true.
For open_ended questions, use an empty choices array and fill in correct_answer with a model answer instead.
PROMPT;

        return $this->callJson($prompt);
    }

    /**
     * Course-scoped study assistant chat. The course material is injected
     * directly into the prompt so the model has no knowledge to draw on
     * beyond what's between the markers (FR.1.5.2).
     */
    public function askTutor(string $courseTitle, string $courseContent, array $history, string $question): string
    {
        $historyText = collect($history)
            ->map(fn ($turn) => ($turn['role'] === 'user' ? 'Student' : 'Tutor') . ': ' . $turn['message'])
            ->implode("\n");

        $historyBlock = $historyText !== '' ? "Conversation so far:\n{$historyText}\n" : '';

        $prompt = <<<PROMPT
You are a study assistant for the college course "{$courseTitle}". You may ONLY use the material between the markers below to answer. If the student asks something not covered by this material, say plainly that it isn't covered in this course's materials yet and suggest they ask their teacher — do not answer from outside knowledge.

=== COURSE MATERIAL ===
{$courseContent}
=== END COURSE MATERIAL ===

{$historyBlock}
Student: {$question}

Reply directly and conversationally as the Tutor, in plain text (no markdown headers, no JSON).
PROMPT;

        return $this->callText($prompt);
    }

    /**
     * Flashcard generation — also restricted to the course material.
     */
    public function generateFlashcards(string $courseTitle, string $courseContent, string $topic, int $count = 10): array
    {
        $prompt = <<<PROMPT
You are creating study flashcards for the college course "{$courseTitle}", based ONLY on the material between the markers below. Do not introduce facts that aren't supported by this material.

=== COURSE MATERIAL ===
{$courseContent}
=== END COURSE MATERIAL ===

Requested topic/focus: {$topic}

Respond with ONLY valid JSON (no markdown fences, no commentary) in exactly this shape:
{
  "flashcards": [
    { "front": "string, a question or term", "back": "string, the answer or definition" }
  ]
}

Create exactly {$count} flashcards covering the requested topic using only the material provided.
PROMPT;

        return $this->callJson($prompt);
    }

    protected function callJson(string $prompt): array
    {
        $text = $this->generate($prompt, ['responseMimeType' => 'application/json', 'maxOutputTokens' => 8192]);

        $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $cleaned = preg_replace('/\s*```$/', '', $cleaned);

        $decoded = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            Log::error('Gemma API returned invalid JSON', [
                'raw_text'   => $text,
                'json_error' => json_last_error_msg(),
            ]);
            throw new RuntimeException('Gemma API returned a response that was not valid JSON.');
        }

        return $decoded;
    }

    protected function callText(string $prompt): string
    {
        return $this->generate($prompt, ['maxOutputTokens' => 1024]);
    }

    protected function generate(string $prompt, array $generationConfig = []): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('GEMINI_API_KEY is not set. Add it to your .env file.');
        }

        $response = Http::timeout(60)->post(
            "{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}",
            [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => array_merge(['temperature' => 0.6], $generationConfig),
            ]
        );

        if ($response->failed()) {
            Log::error('Gemma API request failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new RuntimeException('Gemma API request failed: ' . $response->body());
        }

        $json = $response->json();
        $text = data_get($json, 'candidates.0.content.parts.0.text');
        $finishReason = data_get($json, 'candidates.0.finishReason');

        if (! $text) {
            Log::error('Gemma API returned an empty response', [
                'finishReason' => $finishReason,
                'raw'          => $json,
            ]);
            throw new RuntimeException('Gemma API returned an empty response.');
        }

        return trim($text);
    }
}
