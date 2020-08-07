<?php


namespace Loyalty\Tests;


use DateTime;
use Exception;
use Loyalty\Boundaries\ActionsGatewayInterface;

class TestActionsGateway implements ActionsGatewayInterface
{
    private array $userActions;

    public function __construct(array $userActions = [])
    {
        $this->userActions = $userActions;
    }

    /**
     * @param string $userId
     * @param DateTime $requestedDate
     * @return array
     * @throws Exception
     */
    public function getActionsForUserSinceDate(string $userId, DateTime $requestedDate): array
    {
        return array_filter($this->userActions, function ($action) use ($userId, $requestedDate) {
            $actionBelongsToUser = $action["userId"] === $userId;
            $actionDate = new DateTime($action["expiryDate"]);
            $actionDateIsAfterRequestedDate = $actionDate >= $requestedDate;
            return $actionBelongsToUser && $actionDateIsAfterRequestedDate;
        });
    }
}