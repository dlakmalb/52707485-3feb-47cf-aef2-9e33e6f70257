<?php

use App\Services\DateFormatter;
use Carbon\Carbon;

beforeEach(function () {
    // Arrange
    $this->formatter = new DateFormatter;
});

it('parses the given string date time into a Carbon instance', function () {
    $carbon = $this->formatter->parse('16/12/2021 10:46:00');

    expect($carbon)->toBeInstanceOf(Carbon::class)
        ->and($carbon->year)->toBe(2021)
        ->and($carbon->month)->toBe(12)
        ->and($carbon->day)->toBe(16)
        ->and($carbon->hour)->toBe(10)
        ->and($carbon->minute)->toBe(46)
        ->and($carbon->second)->toBe(0)
        ->and($carbon->timestamp)->toBe(1639651560);
});

it('formats given date to human readable string with time', function () {
    // Act
    $humanReadableFormat = $this->formatter->humanReadableFormat('16/12/2021 10:46:00');

    // Assert
    expect($humanReadableFormat)->toBe('16th December 2021 10:46 AM');
});

it('formats given date to human readable string without time', function () {
    // Act
    $humanReadableFormat = $this->formatter->humanReadableFormat('16/12/2021 10:46:00', false);

    // Assert
    expect($humanReadableFormat)->toBe('16th December 2021');
});
