<?php

namespace App\Services;

class DataLoader
{
    public function __construct(private readonly ?string $dataDir = null) {}

    /**
     * Load all datasets into memory
     *
     * @return array{students: array, questions: array, assessments: array, responses: array}
     */
    public function loadAll(): array
    {
        return [
            'students' => $this->loadFile('students.json'),
            'questions' => $this->loadFile('questions.json'),
            'assessments' => $this->loadFile('assessments.json'),
            'responses' => $this->loadFile('student-responses.json'),
        ];
    }

    /**
     * Load a single JSON file from the data directory.
     */
    private function loadFile(string $filename): array
    {
        $path = ($this->dataDir ?? base_path('data')).DIRECTORY_SEPARATOR.$filename;

        if (! file_exists($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        $decoded = json_decode($raw ?? '[]', true);

        return is_array($decoded) ? $decoded : [];
    }
}
