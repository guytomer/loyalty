<?php


namespace Loyalty\Boundaries;


interface CancelUsageGatewayInterface
{
    public function getAffectedActions(array $affectedActionIds): array;

    public function getUsagesForAlteration(string $usageId): array;

    public function updateActions(array $actions);

    public function updateUsages(array $usages);

    public function cancelUsage(string $usageId);
}