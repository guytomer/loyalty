<?php


namespace Loyalty\Tests;


use DateTime;
use Loyalty\UseCases\Clock;

class TestClock extends Clock
{
    private DateTime $date;

    public function __construct(?DateTime $date = null)
    {
        $this->date = $date ?? new DateTime;
    }

    public function now()
    {
        return $this->date;
    }
}