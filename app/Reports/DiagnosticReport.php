<?php

namespace App\Reports;

use App\Services\DateFormatter;

class DiagnosticReport implements ReportStrategy
{
    public function __construct(private readonly DateFormatter $dateFormatter) {}

    public function build(array $student, array $questions, array $assessments, array $responses): string
    {
        $latestAttempt = $this->latestCompletedAttempt((string) $student['id'], $responses);

        if (! $latestAttempt) {
            return "No completed assessments found for student '{$student['id']}'.";
        }

        // Index questions and assessments by their IDs for quick lookup
        $questionsById = collect($questions)->keyBy('id')->all();
        $assessmentsById = collect($assessments)->keyBy('id')->all();

        $studentName = $this->formatStudentName($student);
        $assessmentName = $assessmentsById[$latestAttempt['assessmentId']]['name'] ?? $latestAttempt['assessmentId'];
        $completedAt = $this->dateFormatter->humanReadableFormat($latestAttempt['completed']);

        [$correct, $total, $byStrand] = $this->evaluateAttempt($latestAttempt, $questionsById);

        $lines = [];
        $lines[] = "{$studentName} recently completed {$assessmentName} assessment on {$completedAt}";
        $lines[] = "He got {$correct} questions right out of {$total}. Details by strand given below:\n";

        foreach ($byStrand as $strand => $stats) {
            $lines[] = "{$strand}: {$stats['correct']} out of {$stats['total']} correct";
        }

        return implode(PHP_EOL, $lines);
    }

    private function latestCompletedAttempt(string $studentId, array $responses): ?array
    {
        $completedResponses = collect($responses)
            ->filter(fn ($r) => $r['student']['id'] === $studentId && ! empty($r['completed']));

        if ($completedResponses->isEmpty()) {
            return null;
        }

        return $completedResponses
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
     * Recompute correctness from responses + questions.config.key.
     * Returns [correct, total, byStrand].
     */
    private function evaluateAttempt(array $attempt, array $questionsById): array
    {
        $overallCorrect = 0;
        $overallTotal = 0;
        $perStrand = [];

        foreach (($attempt['responses'] ?? []) as $responseItem) {
            $questionId = $responseItem['questionId'] ?? null;
            $givenAnswer = $responseItem['response'] ?? null;

            // Skip if the question is unknown or the id is missing
            if ($questionId === null || ! isset($questionsById[$questionId])) {
                continue;
            }

            $question = $questionsById[$questionId];
            $strandName = $question['strand'] ?? 'Unknown';
            $answerKey = $question['config']['key'] ?? null;

            // Determine correctness
            $isCorrect = ($answerKey !== null) && ($givenAnswer === $answerKey);

            // Initialize strand stats if not present
            $perStrand[$strandName] ??= ['correct' => 0, 'total' => 0];

            // Count this question toward the strand and overall totals
            $perStrand[$strandName]['total']++;
            $overallTotal++;

            // Count correctness.
            if ($isCorrect) {
                $perStrand[$strandName]['correct']++;
                $overallCorrect++;
            }
        }

        return [$overallCorrect, $overallTotal, $perStrand];
    }
}
