<?php

use App\Reports\FeedbackReport;
use App\Services\DataLoader;
use App\Services\DateFormatter;

it('shows wrong answers with your answer, right answer, and hint', function () {
    // Arrange
    $loader = new DataLoader;
    $data = $loader->loadAll();

    $student = collect($data['students'])->firstWhere('id', 'student1');
    $questions = $data['questions'];
    $assessments = $data['assessments'];
    $responses = $data['responses'];

    $strategy = new FeedbackReport(new DateFormatter);

    // Act
    $out = $strategy->build($student, $questions, $assessments, $responses);

    // Assert (for 2021 attempt, there should be exactly 1 wrong: the median question)
    expect($out)->toContain('recently completed Numeracy assessment on 16th December 2021 10:46 AM');
    expect($out)->toContain('He got 15 questions right out of 16. Feedback for wrong answers given below');
    expect($out)->toContain("Question: What is the 'median' of the following group of numbers 5, 21, 7, 18, 9?");
    expect($out)->toContain('Your answer: A with value 7');
    expect($out)->toContain('Right answer: B with value 9');
    expect($out)->toContain('Hint: You must first arrange the numbers in ascending order.');
});
