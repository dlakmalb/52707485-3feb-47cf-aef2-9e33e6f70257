<?php

namespace App\Reports;

interface ReportStrategy
{
    /**
     * Build and return the report text to print to the console.
     */
    public function build(array $student, array $questions, array $assessments, array $responses): string;
}
