<?php


namespace Loyalty\UseCases;


use DateTime;

class Clock
{
    public function now() {
        return new DateTime;
    }
}