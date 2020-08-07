<?php

namespace Loyalty\Tests;

use DateTime;
use Loyalty\Boundaries\GetPointsBalanceGatewayInterface;
use Loyalty\UseCases\GetPointsBalance;

class TestGetPointsBalance {
    function getPointsBalanceGateway(array $userActions): GetPointsBalanceGatewayInterface
    {
        return new class($userActions) implements GetPointsBalanceGatewayInterface {
            private array $userActions;
            public function __construct(array $userActions)
            {
                $this->userActions = $userActions;
            }

            public function getPointsForUserSinceDate(string $userId, DateTime $requestedDate): array
            {
                return array_filter($this->userActions, function ($action) use ($userId, $requestedDate) {
                    $actionBelongsToUser = $action["userId"] === $userId;
                    $actionDate = new DateTime($action["expiryDate"]);
                    $actionDateIsAfterRequestedDate = $actionDate >= $requestedDate;
                    return $actionBelongsToUser && $actionDateIsAfterRequestedDate;
                });
            }
        };
    }

    function testZeroBalance(): bool
    {
        $getPointsBalance = new GetPointsBalance($this->getPointsBalanceGateway([]));
        $balance = $getPointsBalance->execute("1", new DateTime("2020-01-01"));
        return $balance === 0;
    }

    function testBalanceOfNonExpiredActions(): bool
    {
        $actions = [
            ["userId" => "1", "awardedPoints" => 50, "activePoints" => 25, "expiryDate" => "2020-01-01"],
            ["userId" => "1", "awardedPoints" => 50, "activePoints" => 20, "expiryDate" => "2020-01-03"],
        ];
        $getPointsBalance = new GetPointsBalance($this->getPointsBalanceGateway($actions));
        $balance = $getPointsBalance->execute("1", new DateTime("2020-01-01"));
        return $balance === 45;
    }

    function testBalanceWithoutExpiredActions(): bool
    {
        $actions = [
            ["userId" => "1", "awardedPoints" => 50, "activePoints" => 25, "expiryDate" => "2020-01-01"],
            ["userId" => "1", "awardedPoints" => 50, "activePoints" => 20, "expiryDate" => "2019-12-31"],
        ];
        $getPointsBalance = new GetPointsBalance($this->getPointsBalanceGateway($actions));
        $balance = $getPointsBalance->execute("1", new DateTime("2020-01-01"));
        return $balance === 25;
    }

    function testBalanceWithNonUserActions(): bool
    {
        $actions = [
            ["userId" => "1", "awardedPoints" => 50, "activePoints" => 25, "expiryDate" => "2020-01-01"],
            ["userId" => "2", "awardedPoints" => 50, "activePoints" => 20, "expiryDate" => "2020-01-01"],
        ];
        $getPointsBalance = new GetPointsBalance($this->getPointsBalanceGateway($actions));
        $balance = $getPointsBalance->execute("1", new DateTime("2020-01-01"));
        return $balance === 25;
    }
}