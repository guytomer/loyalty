<?php


namespace Loyalty\UseCases;

use DateTime;
use Exception;
use Loyalty\Boundaries\ActionsGatewayInterface;

class GetActiveActions
{
    private ActionsGatewayInterface $gateway;

    public function __construct(ActionsGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @param string $userId
     * @param DateTime $requestedDate
     * @return array
     * @throws Exception
     */
    public function execute(string $userId, DateTime $requestedDate): array
    {
        $actions = $this->gateway->getActionsForUserSinceDate($userId, $requestedDate);
        usort($actions, function ($actionA, $actionB) {
            $actionAExpiry = new DateTime($actionA["expiryDate"]);
            $actionBExpiry = new DateTime($actionB["expiryDate"]);
            return $actionAExpiry <=> $actionBExpiry;
        });
        return $actions;
    }
}