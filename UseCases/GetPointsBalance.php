<?php


namespace Loyalty\UseCases;

use DateTime;
use Loyalty\Boundaries\GetPointsBalanceGatewayInterface;

class GetPointsBalance
{
    private GetPointsBalanceGatewayInterface $gateway;

    public function __construct(GetPointsBalanceGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function execute(string $userId, DateTime $requestedDate): int
    {
        $nonExpiredActions = $this->gateway->getPointsForUserSinceDate($userId, $requestedDate);
        $activePointsOfNonExpiredActions = array_column($nonExpiredActions, "activePoints");
        return array_sum($activePointsOfNonExpiredActions);
    }
}