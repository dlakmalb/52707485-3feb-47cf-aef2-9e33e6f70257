<?php

namespace App\Console\Commands;

use App\Reports\DiagnosticReport;
use App\Reports\ReportStrategy;
use App\Services\DataLoader;
use Illuminate\Console\Command;

class GenerateReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:generate
        {student_id? : The student ID (e.g., student1)}
        {report_number? : Report type number (1=Diagnostic, 2=Progress, 3=Feedback)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate assessment reports (Diagnostic, Progress, Feedback) from local JSON data.';

    /**
     * Execute the console command.
     */
    public function handle(DataLoader $loader): int
    {
        $studentId = $this->argument('student_id') ?? $this->ask('Please enter the Student ID');
        $reportNumber = (int) trim((string) ($this->argument('report_number') ?? $this->ask('Report to generate (1 for Diagnostic, 2 for Progress, 3 for Feedback)')));

        $reportTypes = [1 => 'diagnostic', 2 => 'progress', 3 => 'feedback'];
        $reportName = $reportTypes[$reportNumber] ?? null;

        if ($reportName === null) {
            $this->error('Invalid selection. Use 1 for Diagnostic, 2 for Progress, 3 for Feedback.');

            return self::INVALID;
        }

        try {
            $data = $loader->loadAll();
        } catch (\Throwable $e) {
            $this->error('Failed to load data: '.$e->getMessage());

            return self::FAILURE;
        }

        $students = $data['students'] ?? [];
        $questions = $data['questions'] ?? [];
        $assessments = $data['assessments'] ?? [];
        $responses = $data['responses'] ?? [];

        $student = collect($students)->firstWhere('id', $studentId);

        if (! $student) {
            $this->error("Student '{$studentId}' not found.");

            return self::INVALID;
        }

        $strategy = $this->resolveReportStrategy($reportName);

        if (! $strategy) {
            $this->error("Report '{$reportName}' not implemented yet.");

            return self::FAILURE;
        }

        $output = $strategy->build($student, $questions, $assessments, $responses);

        $this->line($output);

        return self::SUCCESS;
    }

    /**
     * Map report name to its strategy instance.
     */
    private function resolveReportStrategy(string $reportName): ?ReportStrategy
    {
        return match ($reportName) {
            'diagnostic' => app(DiagnosticReport::class),
            default => null,
        };
    }
}
