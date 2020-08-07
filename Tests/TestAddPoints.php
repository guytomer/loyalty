<?php

namespace Loyalty\Tests;

use DateTime;
use Loyalty\Boundaries\AddPointsGatewayInterface;
use Loyalty\Exceptions\InvalidPointsException;
use Loyalty\UseCases\AddPoints;

class TestAddPoints {
    function addPointsGateway(): AddPointsGatewayInterface
    {
        return new class implements AddPointsGatewayInterface {
            public ?string $userId = null;
            public ?int $points = null;
            public ?DateTime $expiryDate = null;

            public function addPoints(string $userId, int $points, DateTime $expiryDate)
            {
                $this->userId = $userId;
                $this->points = $points;
                $this->expiryDate = $expiryDate;
            }
        };
    }

    function testAddNegativePoints(): bool
    {
        try {
            $addPoints = new AddPoints($this->addPointsGateway());
            $addPoints->execute("1", -5, new DateTime);
        } catch (InvalidPointsException $exception) {
            return true;
        }
        return false;
    }

    function testAddPoints(): bool
    {
        try {
            $expiryDate = new DateTime;
            $gateway = $this->addPointsGateway();
            $addPoints = new AddPoints($gateway);
            $addPoints->execute("1", 5, $expiryDate);
            return $gateway->userId === "1" && $gateway->points === 5 && $gateway->expiryDate = $expiryDate;
        } catch (InvalidPointsException $exception) {
            return false;
        }
    }
}