<?php

use App\Reports\ProgressReport;
use App\Services\DataLoader;
use App\Services\DateFormatter;

it('generates progress report correctly', function () {
    // Arrange
    $loader = new DataLoader;
    $data = $loader->loadAll();

    $student = collect($data['students'])->firstWhere('id', 'student1');
    $questions = $data['questions'];
    $assessments = $data['assessments'];
    $responses = $data['responses'];

    $strategy = new ProgressReport(new DateFormatter);

    // Act
    $out = $strategy->build($student, $questions, $assessments, $responses);

    // Assert
    expect($out)->toContain('Tony Stark has completed Numeracy assessment 3 times in total. Date and raw score given below');
    expect($out)->toContain('Date: 16th December 2019, Raw Score: 6 out of 16');
    expect($out)->toContain('Date: 16th December 2020, Raw Score: 10 out of 16');
    expect($out)->toContain('Date: 16th December 2021, Raw Score: 15 out of 16');
    expect($out)->toContain('got 9 more correct in the recent completed assessment than the oldest');
});
