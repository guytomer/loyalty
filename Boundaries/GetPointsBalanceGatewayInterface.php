<?php


namespace Loyalty\Boundaries;


use DateTime;

interface GetPointsBalanceGatewayInterface
{
    public function getPointsForUserSinceDate(string $userId, DateTime $requestedDate): array;
}