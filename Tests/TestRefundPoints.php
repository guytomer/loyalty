<?php

namespace Loyalty\Tests;

use DateTime;
use Exception;
use Loyalty\Boundaries\RefundPointsGatewayInterface;
use Loyalty\Exceptions\InvalidPointsException;
use Loyalty\Exceptions\UsageNotFoundException;
use Loyalty\UseCases\RefundPoints;

class TestRefundPoints
{
    function refundPointsGateway(array $usages, array $actions): RefundPointsGatewayInterface
    {
        return new class($usages, $actions) implements RefundPointsGatewayInterface {
            public array $usages;
            public array $actions;
            public array $refund = [];
            public bool $committed = false;

            public function __construct(array $usages, array $actions)
            {
                $this->usages = $usages;
                $this->actions = $actions;
            }

            public function getUsageReductions(string $usageId): array
            {
                $usage = current(array_filter($this->usages, function ($usage) use ($usageId) {
                    return $usage["id"] === $usageId;
                }));
                return $usage ? $usage["reductions"] : [];
            }

            public function getAffectedActions(string $usageId): array
            {
                return $this->actions;
            }

            public function createRefund(string $usageId, int $points)
            {
                $this->refund = ["usageId" => $usageId, "refundedPoints" => $points];
            }

            public function updateUsage(string $usageId, $reductions)
            {
                usort($reductions, function ($reductionA, $reductionB) {
                    return $reductionA["id"] <=> $reductionB["id"];
                });
                $usagesWithoutCurrentUsage = array_filter($this->usages, function($usage) use ($usageId) {
                    return $usage["id"] !== $usageId;
                });
                $this->usages = array_merge($usagesWithoutCurrentUsage, [
                    ["id" => $usageId, "reductions" => $reductions]
                ]);
                usort($this->usages, function ($usageA, $usageB) {
                    return $usageA["id"] <=> $usageB["id"];
                });
            }

            public function updateActions($actions)
            {
                $actionIds = array_column($actions, "id");
                $actionsWithoutUsageActions = array_filter($this->actions, function($action) use ($actionIds) {
                    return !in_array($action["id"], $actionIds);
                });
                $this->actions = array_merge($actionsWithoutUsageActions, $actions);
                usort($this->actions, function ($actionA, $actionB) {
                    $actionAExpiry = new DateTime($actionA["expiryDate"]);
                    $actionBExpiry = new DateTime($actionB["expiryDate"]);
                    return $actionAExpiry <=> $actionBExpiry;
                });
            }

            public function commit()
            {
                $this->committed = true;
            }
        };
    }

    function testRefundNegativePoints(): bool
    {
        try {
            $refundPoints = new RefundPoints($this->refundPointsGateway([], []));
            $refundPoints->execute("1", -5);
        } catch (InvalidPointsException $exception) {
            return true;
        } catch (Exception $e) {
        }
        return false;
    }

    function testRefundPointsWhenNoUsageFound(): bool
    {
        try {
            $refundPoints = new RefundPoints($this->refundPointsGateway([], []));
            $refundPoints->execute("1", 5);
        } catch (UsageNotFoundException $exception) {
            return true;
        } catch (Exception $exception) {
        }
        return false;
    }

    function testRefundPointsExceedUsagePoints(): bool
    {
        try {
            $refundPoints = new RefundPoints($this->refundPointsGateway($this->usages(), $this->actions()));
            $refundPoints->execute("1", 56);
        } catch (InvalidPointsException $exception) {
            return true;
        } catch (Exception $exception) {
        }
        return false;
    }

    function testRefundPartialPoints(): bool
    {
        try {
            $refundPointsGateway = $this->refundPointsGateway($this->usages(), $this->actions());
            $refundPoints = new RefundPoints($refundPointsGateway);
            $refundPoints->execute("1", 30);
            $refundWasCreated = $refundPointsGateway->refund === ["usageId" => "1", "refundedPoints" => 30];
            $actionsWereAltered = $refundPointsGateway->actions === [
                ["id" => "1", "userId" => "1", "awardedPoints" => 50, "activePoints" => 25, "expiryDate" => "2020-01-01"],
                ["id" => "2", "userId" => "1", "awardedPoints" => 50, "activePoints" => 50, "expiryDate" => "2020-01-03"]
            ];
            $usageWasAltered = $refundPointsGateway->usages === [
                ["id" => "1", "reductions" => [
                    ["id" => "1", "usedPoints" => 25]
                ]],
                ["id" => "2", "reductions" => [
                    ["actionId" => "6", "usedPoints" => 30],
                    ["actionId" => "8", "usedPoints" => 20],
                    ["actionId" => "9", "usedPoints" => 20]
                ]]
            ];
            $committed = $refundPointsGateway->committed;
            return $refundWasCreated && $actionsWereAltered && $usageWasAltered && $committed;
        } catch (Exception $e) {
        }
        return false;
    }

    function testRefundFullPoints(): bool
    {
        try {
            $refundPointsGateway = $this->refundPointsGateway($this->usages(), $this->actions());
            $refundPoints = new RefundPoints($refundPointsGateway);
            $refundPoints->execute("1", 55);
            $refundWasCreated = $refundPointsGateway->refund === ["usageId" => "1", "refundedPoints" => 55];
            $actionsWereAltered = $refundPointsGateway->actions === [
                ["id" => "1", "userId" => "1", "awardedPoints" => 50, "activePoints" => 50, "expiryDate" => "2020-01-01"],
                ["id" => "2", "userId" => "1", "awardedPoints" => 50, "activePoints" => 50, "expiryDate" => "2020-01-03"]
            ];
            $usageWasAltered = $refundPointsGateway->usages === [
                ["id" => "1", "reductions" => []],
                ["id" => "2", "reductions" => [
                    ["actionId" => "6", "usedPoints" => 30],
                    ["actionId" => "8", "usedPoints" => 20],
                    ["actionId" => "9", "usedPoints" => 20]
                ]]
            ];
            $committed = $refundPointsGateway->committed;
            return $refundWasCreated && $actionsWereAltered && $usageWasAltered && $committed;
        } catch (Exception $e) {
        }
        return false;
    }

    function usages()
    {
        return [
            ["id" => "1", "reductions" => [
                ["actionId" => "1", "usedPoints" => 40],
                ["actionId" => "2", "usedPoints" => 15]
            ]],
            ["id" => "2", "reductions" => [
                ["actionId" => "6", "usedPoints" => 30],
                ["actionId" => "8", "usedPoints" => 20],
                ["actionId" => "9", "usedPoints" => 20]
            ]]
        ];
    }

    function actions()
    {
        return [
            ["id" => "1", "userId" => "1", "awardedPoints" => 50, "activePoints" => 10, "expiryDate" => "2020-01-01"],
            ["id" => "2", "userId" => "1", "awardedPoints" => 50, "activePoints" => 35, "expiryDate" => "2020-01-03"]
        ];
    }
}