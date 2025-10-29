<?php

namespace App\Services;

use Carbon\Carbon;

class DateFormatter
{
    /**
     * The incoming JSON dates use this format: "16/12/2021 10:46:00"
     */
    private const INPUT_FORMAT = 'd/m/Y H:i:s';

    /**
     * Parse a date string from JSON into a Carbon instance.
     * Falls back to app timezone.
     */
    public function parse(string $dateTime): Carbon
    {
        return Carbon::createFromFormat(self::INPUT_FORMAT, $dateTime);
    }

    /**
     * Human readable format: "16th December 2021 10:46 AM".
     */
    public function humanReadableFormat(string $dateTime): string
    {
        return $this->parse($dateTime)->translatedFormat('jS F Y h:i A');
    }
}
