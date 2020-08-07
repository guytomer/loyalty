<?php


namespace Loyalty\UseCases;

use DateTime;
use Exception;

class GetPointsBalance
{
    private GetActivePoints $getActivePoints;

    public function __construct(GetActivePoints $getActivePoints)
    {
        $this->getActivePoints = $getActivePoints;
    }

    /**
     * @param string $userId
     * @param DateTime $requestedDate
     * @return int
     * @throws Exception
     */
    public function execute(string $userId, DateTime $requestedDate): int
    {
        $nonExpiredActions = $this->getActivePoints->execute($userId, $requestedDate);
        $activePointsOfNonExpiredActions = array_column($nonExpiredActions, "activePoints");
        return array_sum($activePointsOfNonExpiredActions);
    }
}