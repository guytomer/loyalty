<?php

namespace Loyalty\UseCases;

use DateTime;
use Loyalty\Boundaries\AddPointsGatewayInterface;
use Loyalty\Exceptions\InvalidPointsException;


class AddPoints
{

    private AddPointsGatewayInterface $gateway;

    public function __construct(AddPointsGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @param string $userId
     * @param int $points
     * @param DateTime $expiryDate
     * @throws InvalidPointsException
     */
    public function execute(string $userId, int $points, DateTime $expiryDate)
    {
        $this->validateAddedPoints($points);
        $this->gateway->addPoints($userId, $points, $expiryDate);
    }

    /**
     * @param int $points
     * @throws InvalidPointsException
     */
    private function validateAddedPoints(int $points)
    {
        if ($points < 0) throw new InvalidPointsException;
    }
}