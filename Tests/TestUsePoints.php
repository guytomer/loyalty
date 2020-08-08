<?php

namespace Loyalty\Tests;

use DateTime;
use Exception;
use Loyalty\Boundaries\UsePointsGatewayInterface;
use Loyalty\Exceptions\InsufficientPointsException;
use Loyalty\Exceptions\InvalidPointsException;
use Loyalty\UseCases\GetActiveActions;
use Loyalty\UseCases\UsePoints;

class TestUsePoints {
    function usePointsGateway(): UsePointsGatewayInterface
    {
        return new class implements UsePointsGatewayInterface {
            public array $actions = [];
            public array $reductions = [];
            public bool $committed = false;

            public function updateActions(array $actions)
            {
                $this->actions = $actions;
            }

            public function createPointsUsage(array $reductions)
            {
                $this->reductions = $reductions;
            }

            public function commit()
            {
                $this->committed = true;
            }
        };
    }

    function testUsingNegativePoints(): bool
    {
        $usePoints = new UsePoints($this->usePointsGateway(), $this->getActiveActions([]), new TestClock);
        try {
            $usePoints->execute("1", -10);
        } catch (InvalidPointsException $exception) {
            return true;
        } catch (InsufficientPointsException $e) {
        } catch (Exception $e) {
        }
        return false;
    }

    function testUsingInsufficientPoints(): bool
    {
        $actions = [
            ["userId" => "1", "awardedPoints" => 50, "activePoints" => 25, "expiryDate" => "2020-01-01"],
            ["userId" => "1", "awardedPoints" => 50, "activePoints" => 20, "expiryDate" => "2020-01-03"],
        ];
        $usePoints = new UsePoints($this->usePointsGateway(),
            $this->getActiveActions($actions),
            new TestClock(new DateTime("2020-01-01")));
        try {
            $usePoints->execute("1", 46);
        } catch (InsufficientPointsException $exception) {
            return true;
        } catch (InvalidPointsException $e) {
        } catch (Exception $e) {
        }
        return false;
    }

    function testUsingPoints(): bool
    {
        $actions = [
            ["id" => "1", "userId" => "1", "awardedPoints" => 50, "activePoints" => 40, "expiryDate" => "2020-01-01"],
            ["id" => "2", "userId" => "1", "awardedPoints" => 50, "activePoints" => 50, "expiryDate" => "2020-01-03"],
            ["id" => "3", "userId" => "1", "awardedPoints" => 50, "activePoints" => 50, "expiryDate" => "2020-01-04"]
        ];
        $usePointsGateway = $this->usePointsGateway();
        $clock = new TestClock(new DateTime("2020-01-01"));
        $usePoints = new UsePoints($usePointsGateway, $this->getActiveActions($actions), $clock);
        try {
            $usePoints->execute("1", 55);
            $actionsWereAltered = $usePointsGateway->actions == [
                ["id" => "1", "userId" => "1", "awardedPoints" => 50, "activePoints" => 0, "expiryDate" => "2020-01-01"],
                ["id" => "2", "userId" => "1", "awardedPoints" => 50, "activePoints" => 35, "expiryDate" => "2020-01-03"]
            ];
            $usageWasCreated = $usePointsGateway->reductions == [
                ["actionId" => "1", "usedPoints" => 40],
                ["actionId" => "2", "usedPoints" => 15]
            ];
            $transactionWasCommitted = $usePointsGateway->committed;
            return $actionsWereAltered && $usageWasCreated && $transactionWasCommitted;
        } catch (InsufficientPointsException $exception) {
        } catch (InvalidPointsException $e) {
        } catch (Exception $e) {
        }
        return false;
    }

    private function getActiveActions(array $actions): GetActiveActions
    {
        $actionsGateway = new TestActionsGateway($actions);
        return new GetActiveActions($actionsGateway);
    }
}