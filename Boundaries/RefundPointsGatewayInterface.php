<?php


namespace Loyalty\Boundaries;


interface RefundPointsGatewayInterface
{
    public function getUsageReductions(string $usageId): array;

    public function getAffectedActions(string $usageId): array;

    public function createRefund(string $usageId, int $points);

    public function updateUsage(string $usageId, $reductions);

    public function updateActions($actions);

    public function commit();
}