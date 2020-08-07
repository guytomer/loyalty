<?php


namespace Loyalty\Boundaries;


use DateTime;

interface ActionsGatewayInterface
{
    public function getActionsForUserSinceDate(string $userId, DateTime $requestedDate): array;
}