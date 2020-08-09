<?php

namespace Loyalty\Tests;

use Exception;
use Loyalty\Boundaries\CancelUsageGatewayInterface;
use Loyalty\Exceptions\UsageNotFoundException;
use Loyalty\UseCases\CancelUsage;

class TestCancelUsage
{
    function testCancelNonExistingUsage(): bool
    {
        try {
            $cancelUsage = new CancelUsage($this->cancelUsageGateway([], []));
            $cancelUsage->execute("1");
        } catch (UsageNotFoundException $exception) {
            return true;
        } catch (Exception $e) {
        }
        return false;
    }

    function cancelUsageGateway(array $usages, array $actions): CancelUsageGatewayInterface
    {
        return new class($usages, $actions) implements CancelUsageGatewayInterface {
            public array $usages;
            public array $actions;
            public ?string $canceledUsageId = null;

            public function __construct(array $usages, array $actions)
            {
                $this->usages = $usages;
                $this->actions = $actions;
            }

            public function getAffectedActions(array $affectedActionIds): array
            {
                return array_filter($this->actions, function ($action) use ($affectedActionIds) {
                    return in_array($action["id"], $affectedActionIds);
                });
            }

            public function getUsagesForAlteration(string $usageId): array
            {
                $usageIds = array_column($this->usages, "id");
                if (!in_array($usageId, $usageIds)) return [];
                return $this->usages;
            }

            public function updateActions(array $actions)
            {
                $this->actions = $actions;
            }

            public function updateUsages(array $usages)
            {
                $this->usages = $usages;
            }

            public function cancelUsage(string $usageId)
            {
                $this->canceledUsageId = $usageId;
            }
        };
    }

    function testCancelUsage(): bool
    {
        try {
            $cancelUsageGateway = $this->cancelUsageGateway($this->usages(), $this->actions());
            $cancelUsage = new CancelUsage($cancelUsageGateway);
            $cancelUsage->execute("1");
            $usageWasCanceled = $cancelUsageGateway->canceledUsageId === "1";
            $remainingUsagesWereAltered = $cancelUsageGateway->usages === [
                    ["id" => "2", "date" => "2020-01-02", "reductions" => [
                        ["actionId" => "2", "usedPoints" => 50],
                        ["actionId" => "3", "usedPoints" => 20]
                    ]],
                    ["id" => "3", "date" => "2020-01-03", "reductions" => [
                        ["actionId" => "3", "usedPoints" => 10],
                        ["actionId" => "4", "usedPoints" => 20],
                        ["actionId" => "5", "usedPoints" => 10]
                    ]]
                ];
            $actionsWereAltered = $cancelUsageGateway->actions === [
                    ["id" => "1", "userId" => "1", "awardedPoints" => 50, "activePoints" => 50, "expiryDate" => "2020-01-01"],
                    ["id" => "2", "userId" => "1", "awardedPoints" => 50, "activePoints" => 0, "expiryDate" => "2020-01-03"],
                    ["id" => "3", "userId" => "1", "awardedPoints" => 30, "activePoints" => 0, "expiryDate" => "2020-01-04"],
                    ["id" => "4", "userId" => "1", "awardedPoints" => 20, "activePoints" => 0, "expiryDate" => "2020-01-04"],
                    ["id" => "5", "userId" => "1", "awardedPoints" => 50, "activePoints" => 40, "expiryDate" => "2020-01-05"],
                    ["id" => "6", "userId" => "1", "awardedPoints" => 40, "activePoints" => 40, "expiryDate" => "2020-01-05"]
                ];
            return $usageWasCanceled && $remainingUsagesWereAltered && $actionsWereAltered;
        } catch (Exception $e) {
        }
        return false;
    }

    function usages()
    {
        return [
            ["id" => "1", "date" => "2020-01-01", "reductions" => [
                ["actionId" => "1", "usedPoints" => 50],
                ["actionId" => "2", "usedPoints" => 15]
            ]],
            ["id" => "2", "date" => "2020-01-02", "reductions" => [
                ["actionId" => "3", "usedPoints" => 30],
                ["actionId" => "4", "usedPoints" => 20],
                ["actionId" => "5", "usedPoints" => 20]
            ]],
            ["id" => "3", "date" => "2020-01-03", "reductions" => [
                ["actionId" => "5", "usedPoints" => 30],
                ["actionId" => "6", "usedPoints" => 10]
            ]]
        ];
    }

    function actions()
    {
        return [
            ["id" => "1", "userId" => "1", "awardedPoints" => 50, "activePoints" => 10, "expiryDate" => "2020-01-01"],
            ["id" => "2", "userId" => "1", "awardedPoints" => 50, "activePoints" => 35, "expiryDate" => "2020-01-03"],
            ["id" => "3", "userId" => "1", "awardedPoints" => 30, "activePoints" => 0, "expiryDate" => "2020-01-04"],
            ["id" => "4", "userId" => "1", "awardedPoints" => 20, "activePoints" => 0, "expiryDate" => "2020-01-04"],
            ["id" => "5", "userId" => "1", "awardedPoints" => 50, "activePoints" => 0, "expiryDate" => "2020-01-05"],
            ["id" => "6", "userId" => "1", "awardedPoints" => 40, "activePoints" => 30, "expiryDate" => "2020-01-05"]
        ];
    }
}