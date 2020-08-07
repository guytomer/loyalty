<?php


namespace Loyalty\UseCases;

use DateTime;
use Exception;

class GetPointsBalance
{
    private GetActiveActions $getActiveActions;

    public function __construct(GetActiveActions $getActiveActions)
    {
        $this->getActiveActions = $getActiveActions;
    }

    /**
     * @param string $userId
     * @param DateTime $requestedDate
     * @return int
     * @throws Exception
     */
    public function execute(string $userId, DateTime $requestedDate): int
    {
        $nonExpiredActions = $this->getActiveActions->execute($userId, $requestedDate);
        $activePointsOfNonExpiredActions = array_column($nonExpiredActions, "activePoints");
        return array_sum($activePointsOfNonExpiredActions);
    }
}