<?php

/**
 * Shows the error message
 * @param PDOException $e
 * @return void
 */
function printFetchingDataError(PDOException $e): void
{
    echo "Database error: " . $e->getMessage() . "\n";
}

/**
 * Converts timestamp to US short date format
 * @param string|null $date date in format Y-m-d
 * @return string US short date m-d-y
 */
function formatDate(?string $date): string
{
    $format = "m-d-y";
    if ($date === null) {
        return date($format);
    }
    return date($format, strtotime($date));
}
