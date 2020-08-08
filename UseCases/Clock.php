<?php


namespace Loyalty\UseCases;


use DateTime;

class Clock
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