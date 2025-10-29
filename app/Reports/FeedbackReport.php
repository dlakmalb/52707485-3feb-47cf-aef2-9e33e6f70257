<?php

namespace App\Reports;

use App\Services\DateFormatter;

class FeedbackReport implements ReportStrategy
{
    public function __construct(private readonly DateFormatter $dateFormatter) {}

    public function build(array $student, array $questions, array $assessments, array $responses): string
    {
        $latestCompletedAttempt = $this->latestCompletedAttempt((string) $student['id'], $responses);

        if (! $latestCompletedAttempt) {
            return "No completed assessments found for student '{$student['id']}'.";
        }

        // Index questions and assessments by their IDs for quick lookup
        $questionsById = collect($questions)->keyBy('id')->all();
        $assessmentsById = collect($assessments)->keyBy('id')->all();

        // compute totals and wrongs
        [$correct, $total, $wrongs] = $this->evaluateAttempt($latestCompletedAttempt, $questionsById);

        $studentName = $this->formatStudentName($student);
        $assessmentId = $latestCompletedAttempt['assessmentId'];
        $assessmentName = $assessmentsById[$assessmentId]['name'] ?? $assessmentId;
        $completedAt = $this->dateFormatter->humanReadableFormat($latestCompletedAttempt['completed']);

        $lines = [];
        $lines[] = "{$studentName} recently completed {$assessmentName} assessment on {$completedAt}";
        $lines[] = "He got {$correct} questions right out of {$total}. Feedback for wrong answers given below";
        $lines[] = '';

        foreach ($wrongs as $wrong) {
            $lines[] = "Question: {$wrong['question']}";
            $lines[] = "Your answer: {$wrong['given']['label']} with value {$wrong['given']['value']}";
            $lines[] = "Right answer: {$wrong['correct']['label']} with value {$wrong['correct']['value']}";

            if (! empty($wrong['hint'])) {
                $lines[] = "Hint: {$wrong['hint']}";
            }

            $lines[] = ''; // blank line between items
        }

        // If no wrong answers, add a message.
        if (empty($wrongs)) {
            $lines[] = 'Great job! No wrong answers to show.';
        }

        return implode(PHP_EOL, $lines);
    }

    private function latestCompletedAttempt(string $studentId, array $responses): ?array
    {
        return collect($responses)
            ->filter(fn ($r) => $r['student']['id'] === $studentId && ! empty($r['completed']))
            ->sortByDesc(fn ($r) => $this->dateFormatter->parse($r['completed'])->timestamp)
            ->first();
    }

    private function formatStudentName(array $student): string
    {
        $firstName = $student['firstName'] ?? '';
        $lastName = $student['lastName'] ?? '';

        return trim("{$firstName} {$lastName}");
    }

    /**
     * Returns [correct, total, wrongs[]]
     * wrongs[] = [
     *   'question' => string,
     *   'given' => ['label' => string, 'value' => string],
     *   'correct' => ['label' => string, 'value' => string],
     *   'hint' => string,
     * ]
     */
    private function evaluateAttempt(array $attempt, array $questionsById): array
    {
        $overallCorrect = 0;
        $overallTotal = 0;
        $wrongs = [];

        foreach (($attempt['responses'] ?? []) as $responseItem) {
            $questionId = $responseItem['questionId'] ?? null;
            $givenAnswer = $responseItem['response'] ?? null;

            // Skip if the question is unknown or the id is missing
            if (! $questionId || ! isset($questionsById[$questionId])) {
                continue;
            }

            $question = $questionsById[$questionId];
            $questionConfig = $question['config'] ?? [];
            $answerKey = $questionConfig['key'] ?? null;
            $options = $questionConfig['options'] ?? [];

            $givenOption = $this->findOption($options, (string) $givenAnswer);
            $correctOption = $this->findOption($options, (string) $answerKey);

            $overallTotal++;

            if ($answerKey !== null && $givenAnswer === $answerKey) {
                $overallCorrect++;

                continue;
            }

            // collect wrong
            $wrongs[] = [
                'question' => $question['stem'] ?? $questionId,
                'given' => [
                    'label' => $givenOption['label'] ?? 'N/A',
                    'value' => $givenOption['value'] ?? 'N/A',
                ],
                'correct' => [
                    'label' => $correctOption['label'] ?? 'N/A',
                    'value' => $correctOption['value'] ?? 'N/A',
                ],
                'hint' => $questionConfig['hint'] ?? '',
            ];
        }

        return [$overallCorrect, $overallTotal, $wrongs];
    }

    /**
     * Find an option by its id inside question.config.options.
     */
    private function findOption(array $options, ?string $id): ?array
    {
        return $id === null ? null : collect($options)->firstWhere('id', $id);
    }
}
