<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * EDITH — AI Service for TUP-LMS
 *
 * Key rotation strategy:
 *   - Models are tried in order within each API key.
 *   - Only when all models under a key are exhausted (quota hit) does it
 *     move to the next API key.
 *   - After all keys are exhausted, it wraps back around (next UTC day
 *     quotas reset, so the first key + model is tried again).
 *
 * State is stored in Laravel's cache under 'edith_state' so it persists
 * across requests without needing a DB table.
 */
class EdithService
{
    // ---------------------------------------------------------------
    // Model pool — tried in order before advancing to next API key.
    // ---------------------------------------------------------------
    protected array $models = [
        'gemini-2.5-flash',
        'gemini-2.5-flash-lite',
        'gemini-2.0-flash',
        'gemini-2.0-flash-lite',
    ];

    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    // Cache key and TTL (26 hours so state outlives a UTC day boundary)
    protected string $cacheKey = 'edith_state';
    protected int    $cacheTtl = 93600; // 26 hours in seconds

    // ---------------------------------------------------------------
    // Public AI methods (same API as GemmaService — drop-in)
    // ---------------------------------------------------------------

    public function generateOutline(string $topic, ?string $notes = null): array
    {
        $notes = $notes ?: 'None provided.';

        $prompt = <<<PROMPT
You are EDITH, an AI instructional designer helping a college teacher draft a new course for a Philippine technological university.

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
You are EDITH, an AI writing college-level STUDY REVIEWER NOTES for students at a Philippine technological university.

Course: {$courseTitle}
Topic: {$moduleTitle}

Context:
{$summary}

Write a complete, well-structured reviewer lesson for students using clean Markdown formatting.

REQUIREMENTS:
- 600–800 words of actual content
- Clear, detailed, and educational
- Helps students understand AND prepare for exams
- Includes explanations and examples

STRUCTURE — use these exact Markdown headings in order:
## Definition
## Why It Matters
## Key Concepts
## How It Works
## Examples
## Common Mistakes
## Exam Focus

FORMATTING RULES:
- Use ## for section headings (not # or ###)
- Use **bold** for key terms on first use
- Use bullet lists (- item) for grouped concepts or steps
- Use numbered lists (1. step) for sequential procedures
- Use `inline code` only for actual code, commands, or technical syntax
- Use > blockquote for important notes or warnings
- Tables are allowed when comparing concepts side by side
- Keep paragraphs short (2–4 sentences max)
- Do NOT include a top-level title (the topic title is already shown above the content)
- Do NOT include any preamble, intro phrase, or closing like "I hope this helps"
- Do NOT output JSON, code fences around the whole response, or meta-commentary
PROMPT;

        $content = $this->callText($prompt);
        return ['content' => $content];
    }

    public function generateActivity(string $courseTitle, string $moduleTitle, string $courseContent = ''): array
    {
        $contentBlock = $courseContent !== '' && $courseContent !== 'No lesson content has been added to this course yet.'
            ? "=== COURSE LESSON CONTENT ===\n{$courseContent}\n=== END COURSE LESSON CONTENT ===\n\n"
            : '';

        $prompt = <<<PROMPT
You are EDITH, an AI designing a learning activity for the topic "{$moduleTitle}" in the course "{$courseTitle}".

{$contentBlock}STRICT RULES:
- This activity must be based ENTIRELY on the lesson content above.
- Every instruction, question, or task must reference only concepts explicitly taught in the lessons.
- Do NOT introduce new topics, outside facts, or concepts not present in the lesson content.

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

    public function generateAssessment(string $courseTitle, string $moduleTitle, string $kind = 'quiz', int $numQuestions = 5, string $courseContent = ''): array
    {
        $label = $kind === 'exam' ? 'exam' : 'short quiz';

        $contentBlock = $courseContent !== '' && $courseContent !== 'No lesson content has been added to this course yet.'
            ? "=== COURSE LESSON CONTENT ===\n{$courseContent}\n=== END COURSE LESSON CONTENT ===\n\n"
            : '';

        $requestCount = $numQuestions + 2;

        $prompt = <<<PROMPT
You are EDITH, an AI writing a {$label} for the topic "{$moduleTitle}" in the course "{$courseTitle}".

{$contentBlock}STRICT RULES:
- Every single question must test ONLY knowledge explicitly covered in the lesson content above.
- Do NOT ask about concepts, facts, formulas, or topics not present in the lessons.
- Choices for multiple_choice questions must also be grounded in the lesson content.

// AFTER
Create exactly {$requestCount} questions. ALL questions must be multiple_choice only — no open_ended questions regardless of whether this is a quiz or exam.

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
      "correct_answer": "string or null"
    }
  ]
}

For multiple_choice: exactly 4 choices labeled A, B, C, D with exactly one is_correct: true.
For open_ended: empty choices array, fill correct_answer with a model answer.
PROMPT;

        $result = $this->callJson($prompt, 8192);

        if (isset($result['questions']) && count($result['questions']) > $numQuestions) {
            $result['questions'] = array_slice($result['questions'], 0, $numQuestions);
        }

        return $result;
    }

    public function askTutor(string $courseTitle, string $courseContent, array $history, string $question): string
    {
        $historyText = collect($history)
            ->map(fn ($turn) => ($turn['role'] === 'user' ? 'Student' : 'EDITH') . ': ' . $turn['message'])
            ->implode("\n");

        $historyBlock = $historyText !== '' ? "Conversation so far:\n{$historyText}\n" : '';

        $prompt = <<<PROMPT
You are EDITH, a smart and helpful AI study tutor for the college course "{$courseTitle}".

The course material is provided below. Use it as your primary knowledge source.

=== COURSE MATERIAL ===
{$courseContent}
=== END COURSE MATERIAL ===

YOUR RULES:
1. Only discuss topics present in the course material. If a topic is not covered, say so plainly.
2. If a topic IS in the material, explain it clearly with examples, analogies, or step-by-step breakdowns.
3. Be conversational, encouraging, and clear — like a patient tutor.
4. Always refer to yourself as EDITH.

OUTPUT FORMAT — follow exactly:
- Output ONLY the answer. No prefix, no label, no "EDITH:" at the start.
- No preamble: do NOT start with "Sure!", "Of course!", "Great question!", "Certainly!", "Absolutely!", or any similar phrase.
- No sign-off: do NOT end with "I hope this helps!", "Feel free to ask!", "Let me know if you have questions!", or similar.
- Use **bold** for key terms on first mention.
- Use bullet or numbered lists for steps or grouped items.
- Use ## headings ONLY for long structured explanations — not for short answers.
- Use > blockquotes for warnings or important notes.
- Use tables only when comparing two or more concepts side by side.
- Use `code` only for actual syntax or commands.
- Do NOT wrap the whole response in a code fence.
- Short questions get short answers. Do not pad or over-explain.

{$historyBlock}
Student: {$question}

EDITH:
PROMPT;

        $raw = $this->callText($prompt);
        return $this->cleanTutorResponse($raw);
    }

    /**
     * Strip common junk patterns from EDITH tutor responses:
     * - Leading "EDITH:" label
     * - Preamble filler phrases
     * - Trailing sign-off phrases
     * - Wrapping code fences around the whole response
     */
    private function cleanTutorResponse(string $text): string
    {
        $text = trim($text);

        // Remove leading "EDITH:" prefix (case-insensitive, with optional space/newline)
        $text = preg_replace('/^EDITH\s*:\s*/i', '', $text);

        // Remove leading preamble phrases (whole first sentence if it's filler)
        $preambles = [
            '/^(Sure|Of course|Certainly|Absolutely|Great question|Good question|That\'s a great question|No problem|Happy to help|I\'d be happy to|Let me explain|Let me help)[^a-z]*[!.,]?\s*/i',
        ];
        foreach ($preambles as $pattern) {
            $text = preg_replace($pattern, '', $text);
        }

        // Remove trailing sign-off phrases (last line if it's filler)
        $signoffs = [
            '/[\n\r]+\s*(I hope (this|that) helps?!?\.?|Feel free to ask( (me )?anything)?!?\.?|Let me know if you (have|need) (any )?(more |other )?questions?!?\.?|Don\'t hesitate to ask!?\.?|If you have (any )?more questions?,? feel free to ask!?\.?|Is there anything else you\'d like to know\??\.?)\s*$/i',
        ];
        foreach ($signoffs as $pattern) {
            $text = preg_replace($pattern, '', $text);
        }

        // Remove wrapping code fence (```...``` around the entire response)
        $text = preg_replace('/^```[a-z]*\r?\n([\s\S]*?)\r?\n```$/i', '$1', trim($text));

        return trim($text);
    }

    public function generateFlashcards(string $courseTitle, string $courseContent, string $topic, int $count = 10): array
    {
        $prompt = <<<PROMPT
You are EDITH, an AI creating study flashcards for the college course "{$courseTitle}", based ONLY on the material between the markers below.

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

    // ---------------------------------------------------------------
    // Core HTTP layer — with rotation
    // ---------------------------------------------------------------

    protected function callJson(string $prompt, int $maxTokens = 4096): array
    {
        $strictPrompt = <<<PROMPT
You are a JSON API.

CRITICAL RULES:
- Return ONLY valid JSON.
- No explanations, reasoning, planning, or self-correction.
- No markdown, no code fences, no text before or after the JSON.
- First character must be {
- Last character must be }

{$prompt}
PROMPT;

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $text = $this->generate($strictPrompt, [
                'responseMimeType' => 'application/json',
                'temperature'      => 0,
                'topP'             => 0.1,
                'topK'             => 1,
                'maxOutputTokens'  => $maxTokens,
                'thinkingConfig'   => ['thinkingBudget' => 0],
            ]);

            $cleaned = trim($text);
            $decoded = json_decode($cleaned, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            Log::warning('EDITH JSON attempt failed', [
                'attempt'  => $attempt,
                'error'    => json_last_error_msg(),
                'response' => substr($cleaned, 0, 2000),
            ]);
        }

        throw new RuntimeException('EDITH: Gemini API returned a response that was not valid JSON after 3 attempts.');
    }

    protected function callText(string $prompt): string
    {
        return $this->generate($prompt, [
            'maxOutputTokens' => 8192,
            'temperature'     => 0.3,
        ]);
    }

    /**
     * Core generate method — rotates API keys and models on quota exhaustion.
     *
     * State shape stored in cache:
     * {
     *   "key_index":   int,   // current index into config('edith.api_keys')
     *   "model_index": int,   // current index into $this->models
     *   "exhausted":   [      // list of [key_index, model_index] pairs fully used up
     *     [0, 0], [0, 1], ...
     *   ]
     * }
     */
    protected function generate(string $prompt, array $generationConfig = []): string
    {
        $apiKeys = config('edith.api_keys', []);

        if (empty($apiKeys)) {
            throw new RuntimeException('EDITH: No API keys configured. Add EDITH_API_KEY_1 (and optionally more) to your .env.');
        }

        set_time_limit(300);

        // Load or initialise rotation state
        $state = Cache::store('file')->get($this->cacheKey, [
            'key_index'   => 0,
            'model_index' => 0,
            'exhausted'   => [],
        ]);

        $totalKeys   = count($apiKeys);
        $totalModels = count($this->models);
        $totalSlots  = $totalKeys * $totalModels;

        // Safety: if all slots exhausted, reset so next request starts fresh
        // (daily quotas may have reset by then)
        if (count($state['exhausted']) >= $totalSlots) {
            Log::warning('EDITH: All API keys and models exhausted. Resetting rotation state.');
            $state = ['key_index' => 0, 'model_index' => 0, 'exhausted' => []];
            Cache::store('file')->put($this->cacheKey, $state, $this->cacheTtl);
        }

        // Try slots starting from current position; give up after checking every slot
        $attempts = 0;

        while ($attempts < $totalSlots) {
            $keyIndex   = $state['key_index'];
            $modelIndex = $state['model_index'];

            // Skip already-exhausted slots
            if (in_array([$keyIndex, $modelIndex], $state['exhausted'], true)) {
                $state = $this->advanceSlot($state, $totalKeys, $totalModels);
                $attempts++;
                continue;
            }

            $apiKey = $apiKeys[$keyIndex] ?? null;
            $model  = $this->models[$modelIndex];

            if (empty($apiKey)) {
                // Key missing — treat as exhausted
                $state = $this->markExhausted($state, $keyIndex, $modelIndex, $totalKeys, $totalModels);
                Cache::store('file')->put($this->cacheKey, $state, $this->cacheTtl);
                $attempts++;
                continue;
            }

            Log::info('EDITH: Attempting request', [
                'key_index'   => $keyIndex,
                'model_index' => $modelIndex,
                'model'       => $model,
            ]);

            $response = Http::timeout(300)->post(
                "{$this->baseUrl}/{$model}:generateContent?key={$apiKey}",
                [
                    'contents'         => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => array_merge([
                        'temperature'     => 0.6,
                        'maxOutputTokens' => 4096,
                    ], $generationConfig),
                ]
            );

            // --- Quota / rate-limit errors → exhaust this slot, advance ---
            if ($response->status() === 429 || $this->isQuotaError($response)) {
                Log::warning('EDITH: Quota exhausted', [
                    'key_index' => $keyIndex,
                    'model'     => $model,
                    'status'    => $response->status(),
                ]);

                $state = $this->markExhausted($state, $keyIndex, $modelIndex, $totalKeys, $totalModels);
                Cache::store('file')->put($this->cacheKey, $state, $this->cacheTtl);
                $attempts++;
                continue;
            }

            // --- Other HTTP errors → throw immediately (not a quota issue) ---
            // -------------------------------------------------------
// Invalid/retired model (404)
// Skip this model and continue rotation.
// -------------------------------------------------------
            if ($response->status() === 404) {

                Log::warning('EDITH: Model unavailable. Skipping model.', [
                    'key_index'   => $keyIndex,
                    'model_index' => $modelIndex,
                    'model'       => $model,
                ]);

                $state = $this->markExhausted(
                    $state,
                    $keyIndex,
                    $modelIndex,
                    $totalKeys,
                    $totalModels
                );

                Cache::store('file')->put(
                    $this->cacheKey,
                    $state,
                    $this->cacheTtl
                );

                $attempts++;
                continue;
            }

// -------------------------------------------------------
// Invalid API key
// Skip current key/model.
// -------------------------------------------------------
            if ($response->status() === 401 || $response->status() === 403) {

                Log::warning('EDITH: API key rejected. Skipping.', [
                    'key_index'   => $keyIndex,
                    'model_index' => $modelIndex,
                    'model'       => $model,
                    'status'      => $response->status(),
                ]);

                $state = $this->markExhausted(
                    $state,
                    $keyIndex,
                    $modelIndex,
                    $totalKeys,
                    $totalModels
                );

                Cache::store('file')->put(
                    $this->cacheKey,
                    $state,
                    $this->cacheTtl
                );

                $attempts++;
                continue;
            }

// -------------------------------------------------------
// Temporary Google server errors
// Don't rotate immediately.
// -------------------------------------------------------
            if (in_array($response->status(), [500, 502, 503, 504])) {

                Log::warning('EDITH: Temporary Gemini server error.', [
                    'status' => $response->status(),
                    'model'  => $model,
                ]);

                sleep(2);
                continue;
            }

// -------------------------------------------------------
// Any other HTTP error
// -------------------------------------------------------
            if ($response->failed()) {

                Log::error('EDITH: API request failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                throw new RuntimeException(
                    'EDITH: Gemini API request failed: ' . $response->body()
                );
            }

            // --- Success ---
            $json  = $response->json();
            $parts = data_get($json, 'candidates.0.content.parts', []);
            $text  = '';

            foreach ($parts as $part) {
                if (($part['thought'] ?? false) === true) {
                    continue;
                }
                if (!empty($part['text'])) {
                    $text .= $part['text'];
                }
            }

            $text         = trim($text);
            $finishReason = data_get($json, 'candidates.0.finishReason');

            if (!$text) {
                Log::error('EDITH: API returned empty text', [
                    'finishReason' => $finishReason,
                    'model'        => $model,
                ]);
                throw new RuntimeException('EDITH: Gemini API returned an empty response.');
            }

            if ($finishReason === 'MAX_TOKENS') {
                Log::warning('EDITH: Response truncated (MAX_TOKENS)', ['model' => $model]);
            }

            return $text;
        }

        throw new RuntimeException('EDITH: All API keys and models are exhausted. Please try again later or add more API keys.');
    }

    // ---------------------------------------------------------------
    // Rotation helpers
    // ---------------------------------------------------------------

    /**
     * Mark the current [keyIndex, modelIndex] slot as exhausted and advance.
     * Strategy: exhaust ALL models for the current key before moving to next key.
     */
    protected function markExhausted(array $state, int $keyIndex, int $modelIndex, int $totalKeys, int $totalModels): array
    {
        if (!in_array([$keyIndex, $modelIndex], $state['exhausted'], true)) {
            $state['exhausted'][] = [$keyIndex, $modelIndex];
        }

        return $this->advanceSlot($state, $totalKeys, $totalModels);
    }

    /**
     * Advance to the next non-exhausted slot.
     * Model index cycles first (exhaust all models per key), then key index.
     */
    protected function advanceSlot(array $state, int $totalKeys, int $totalModels): array
    {
        $keyIndex   = $state['key_index'];
        $modelIndex = $state['model_index'];

        // Try next model within the same key first
        $nextModel = $modelIndex + 1;
        if ($nextModel < $totalModels) {
            $state['model_index'] = $nextModel;
            return $state;
        }

        // All models for this key done — move to next key, reset model index
        $nextKey = ($keyIndex + 1) % $totalKeys;
        $state['key_index']   = $nextKey;
        $state['model_index'] = 0;

        return $state;
    }

    /**
     * Detect quota errors from the response body (Gemini returns 200 with error details sometimes).
     */
    protected function isQuotaError($response): bool
    {
        if ($response->status() === 429) {
            return true;
        }

        $body = strtolower($response->body());
        $quotaKeywords = [
            'quota exceeded',
            'resource_exhausted',
            'rateLimitExceeded',
            'daily limit exceeded',
            'requests per day',
            'quota_exceeded',
        ];

        foreach ($quotaKeywords as $keyword) {
            if (str_contains($body, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    // ---------------------------------------------------------------
    // Utility — for admin/debug use
    // ---------------------------------------------------------------

    /** Returns current rotation state (for debugging/admin panels). */
    public function getRotationState(): array
    {
        $state   = Cache::store('file')->get($this->cacheKey, ['key_index' => 0, 'model_index' => 0, 'exhausted' => []]);
        $apiKeys = config('edith.api_keys', []);

        return [
            'current_key_index'   => $state['key_index'],
            'current_model'       => $this->models[$state['model_index']] ?? 'unknown',
            'total_keys'          => count($apiKeys),
            'total_models'        => count($this->models),
            'exhausted_slots'     => $state['exhausted'],
            'models'              => $this->models,
        ];
    }

    /** Manually reset rotation state (call from tinker or an admin route). */
    public function resetRotationState(): void
    {
        Cache::store('file')->forget($this->cacheKey);
        Log::info('EDITH: Rotation state manually reset.');
    }
}
