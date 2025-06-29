<?php

namespace Core;

use Datetime;
use DateInterval;

class DateTimeHelper
{
    private $dateTime;

    // Constructor accepts a date string or defaults to now
    public function __construct($date = 'now')
    {
        $this->dateTime = new DateTime($date);
    }

    // Get current timestamp
    public function getTimestamp()
    {
        return $this->dateTime->getTimestamp();
    }

    // Set from timestamp
    public function setFromTimestamp($timestamp)
    {
        $this->dateTime->setTimestamp($timestamp);
    }

    // Format the date
    public function format($format = 'Y-m-d H:i:s')
    {
        return $this->dateTime->format($format);
    }

    // Add time using interval (e.g., P1D for 1 day)
    public function add($interval)
    {
        $this->dateTime->add(new DateInterval($interval));
    }

    // Subtract time using interval (e.g., P1M for 1 month)
    public function subtract($interval)
    {
        $this->dateTime->sub(new DateInterval($interval));
    }

    // Get difference from another date
    public function diff($otherDate)
    {
        $other = new DateTime($otherDate);
        return $this->dateTime->diff($other);
    }

    // Static method to convert a date string to timestamp
    public static function toTimestamp($date)
    {
        return (new DateTime($date))->getTimestamp();
    }

    // Static method to convert timestamp to formatted date
    public static function fromTimestamp($timestamp, $format = 'Y-m-d H:i:s')
    {
        return (new DateTime())->setTimestamp($timestamp)->format($format);
    }

    // Format date as "Member since: Month DD, YYYY"
    public function getMemberSince()
    {
        return 'Member since: ' . $this->dateTime->format('F j, Y');
    }
}
