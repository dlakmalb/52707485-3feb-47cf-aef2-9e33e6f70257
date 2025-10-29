<?php

use App\Reports\DiagnosticReport;
use App\Services\DataLoader;
use App\Services\DateFormatter;

it('prints latest diagnostic summary and strand breakdown', function () {
    // Arrange
    $loader = new DataLoader;
    $data = $loader->loadAll();

    $student = collect($data['students'])->firstWhere('id', 'student1');
    $questions = $data['questions'];
    $assessments = $data['assessments'];
    $responses = $data['responses'];

    $strategy = new DiagnosticReport(new DateFormatter);

    // Act
    $out = $strategy->build($student, $questions, $assessments, $responses);

    // Assert
    expect($out)->toContain('Tony Stark recently completed Numeracy assessment on 16th December 2021 10:46 AM');
    expect($out)->toContain('He got 15 questions right out of 16');
    expect($out)->toContain('Number and Algebra:');
    expect($out)->toContain('Measurement and Geometry:');
    expect($out)->toContain('Statistics and Probability:');
});
