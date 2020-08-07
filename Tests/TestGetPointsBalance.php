<?php

namespace Loyalty\Tests;

use DateTime;
use Exception;
use Loyalty\UseCases\GetActivePoints;
use Loyalty\UseCases\GetPointsBalance;

class TestGetPointsBalance
{
    function testZeroBalance(): bool
    {
        $getPointsBalance = new GetPointsBalance($this->getActivePoints([]));
        try {
            $balance = $getPointsBalance->execute("1", new DateTime("2020-01-01"));
            return $balance === 0;
        } catch (Exception $e) {
        }
        return false;
    }

    function testBalanceOfNonExpiredActions(): bool
    {
        $actions = [
            ["userId" => "1", "awardedPoints" => 50, "activePoints" => 25, "expiryDate" => "2020-01-01"],
            ["userId" => "1", "awardedPoints" => 50, "activePoints" => 20, "expiryDate" => "2020-01-03"],
        ];
        $getPointsBalance = new GetPointsBalance($this->getActivePoints($actions));
        try {
            $balance = $getPointsBalance->execute("1", new DateTime("2020-01-01"));
            return $balance === 45;
        } catch (Exception $e) {
        }
        return false;
    }

    function testBalanceWithoutExpiredActions(): bool
    {
        $actions = [
            ["userId" => "1", "awardedPoints" => 50, "activePoints" => 25, "expiryDate" => "2020-01-01"],
            ["userId" => "1", "awardedPoints" => 50, "activePoints" => 20, "expiryDate" => "2019-12-31"],
        ];
        $getPointsBalance = new GetPointsBalance($this->getActivePoints($actions));
        try {
            $balance = $getPointsBalance->execute("1", new DateTime("2020-01-01"));
            return $balance === 25;
        } catch (Exception $e) {
        }
        return false;
    }

    function testBalanceWithNonUserActions(): bool
    {
        $actions = [
            ["userId" => "1", "awardedPoints" => 50, "activePoints" => 25, "expiryDate" => "2020-01-01"],
            ["userId" => "2", "awardedPoints" => 50, "activePoints" => 20, "expiryDate" => "2020-01-01"],
        ];
        $getPointsBalance = new GetPointsBalance($this->getActivePoints($actions));
        try {
            $balance = $getPointsBalance->execute("1", new DateTime("2020-01-01"));
            return $balance === 25;
        } catch (Exception $e) {
        }
        return false;
    }

    private function getActivePoints(array $actions): GetActivePoints
    {
        $actionsGateway = new TestActionsGateway($actions);
        return new GetActivePoints($actionsGateway);
    }
}