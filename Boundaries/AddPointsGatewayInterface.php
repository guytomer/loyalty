<?php


namespace Loyalty\Boundaries;


use DateTime;

interface AddPointsGatewayInterface
{
    public function addPoints(string $userId, int $points, DateTime $expiryDate);
}