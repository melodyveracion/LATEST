<?php

use Illuminate\Support\Carbon;

if (!function_exists('utc_now')) {
    /**
     * Current time in UTC for storing in the database.
     * Prefer now() for user-facing timestamps (submitted_at, confirmed_at, etc.) so display matches local time.
     */
    function utc_now(): Carbon
    {
        return Carbon::now('UTC');
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Format a datetime value from the database for display.
     * Assumes the database stores times in the application timezone (see APP_TIMEZONE in .env, e.g. Asia/Manila).
     *
     * @param mixed $value datetime string, Carbon instance, or null
     * @param string|null $format format string (default: 'M d, Y h:i A')
     * @return string|null formatted string or null if empty
     */
    function format_datetime($value, ?string $format = null): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $format = $format ?? 'M d, Y h:i A';
        $appTz = config('app.timezone', 'Asia/Manila');
        try {
            $carbon = $value instanceof Carbon
                ? $value->copy()->setTimezone($appTz)
                : Carbon::parse($value, $appTz);
            return $carbon->format($format);
        } catch (\Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('format_date')) {
    /**
     * Format a date value (no time) for display in the application timezone.
     *
     * @param mixed $value datetime string, Carbon instance, or null
     * @param string|null $format format string (default: 'M d, Y')
     * @return string|null formatted string or null if empty
     */
    function format_date($value, ?string $format = null): ?string
    {
        return format_datetime($value, $format ?? 'M d, Y');
    }
}
