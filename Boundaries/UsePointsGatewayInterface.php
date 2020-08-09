<?php


namespace Loyalty\Boundaries;


use DateTime;

interface UsePointsGatewayInterface
{
    public function createPointsUsage(string $userId, array $reductions, DateTime $date): string;

    public function updateActions(array $actions);
}