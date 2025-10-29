<?php

namespace App\Reports;

use App\Services\DateFormatter;
use Illuminate\Support\Collection;

class ProgressReport implements ReportStrategy
{
    public function __construct(private readonly DateFormatter $dateFormatter) {}

    public function build(array $student, array $questions, array $assessments, array $responses): string
    {
        $completedAttempts = $this->completedAttempts((string) $student['id'], $responses);

        if ($completedAttempts->isEmpty()) {
            return "No completed assessments found for student '{$student['id']}'.";
        }

        // Index questions and assessments by their IDs for quick lookup
        $questionsById = collect($questions)->keyBy('id')->all();
        $assessmentsById = collect($assessments)->keyBy('id')->all();

        // Score each attempt
        $scored = $completedAttempts
            ->map(function (array $attempt) use ($questionsById) {
                [$correct, $total] = $this->evaluateAttempt($attempt, $questionsById);

                return [
                    'completed' => $attempt['completed'],
                    'assessmentId' => $attempt['assessmentId'],
                    'correct' => $correct,
                    'total' => $total,
                ];
            })
            ->values();

        $studentName = $this->formatStudentName($student);
        $assessmentName = $assessmentsById[$scored->first()['assessmentId']]['name'] ?? $scored->first()['assessmentId'];

        $lines = [];
        $lines[] = "{$studentName} has completed {$assessmentName} assessment {$scored->count()} times in total. Date and raw score given below:\n";

        foreach ($scored as $s) {
            $lines[] = 'Date: '.$this->dateFormatter->humanReadableFormat($s['completed'], false).
                ", Raw Score: {$s['correct']} out of {$s['total']}";
        }

        // Calculate improvement. (most recent - oldest)
        $oldest = $scored->first()['correct'];
        $latest = $scored->last()['correct'];
        $diff = $latest - $oldest;

        $lines[] = '';
        $lines[] = "{$studentName} got {$diff} more correct in the recent completed assessment than the oldest";

        return implode(PHP_EOL, $lines);
    }

    private function completedAttempts(string $studentId, array $responses): Collection
    {
        return collect($responses)
            ->filter(fn ($r) => $r['student']['id'] === $studentId && ! empty($r['completed']))
            ->sortBy(fn ($r) => $this->dateFormatter->parse($r['completed'])->timestamp)
            ->values();
    }

    private function formatStudentName(array $student): string
    {
        $firstName = $student['firstName'] ?? '';
        $lastName = $student['lastName'] ?? '';

        return trim("{$firstName} {$lastName}");
    }

    /**
     * Returns [correct, total]
     */
    private function evaluateAttempt(array $attempt, array $questionsById): array
    {
        $overallCorrect = 0;
        $overallTotal = 0;

        foreach (($attempt['responses'] ?? []) as $responseItem) {
            $questionId = $responseItem['questionId'] ?? null;
            $givenAnswer = $responseItem['response'] ?? null;

            // Skip if question not found or id missing
            if (! $questionId || ! isset($questionsById[$questionId])) {
                continue;
            }

            $answerKey = $questionsById[$questionId]['config']['key'] ?? null;
            $overallTotal++;

            if ($answerKey !== null && $givenAnswer === $answerKey) {
                $overallCorrect++;
            }
        }

        return [$overallCorrect, $overallTotal];
    }
}
