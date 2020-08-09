<?php


namespace Loyalty\UseCases;


use DateTime;
use Exception;
use Loyalty\Boundaries\CancelUsageGatewayInterface;
use Loyalty\Exceptions\UsageNotFoundException;

class CancelUsage
{
    use ActionReducer;

    private CancelUsageGatewayInterface $cancelUsageGateway;
    private array $actions;
    private array $usagesForRecreation;

    public function __construct(CancelUsageGatewayInterface $cancelUsageGateway)
    {
        $this->cancelUsageGateway = $cancelUsageGateway;
    }

    /**
     * @param string $usageId
     * @throws UsageNotFoundException
     * @throws Exception
     */
    public function execute(string $usageId)
    {
        $usagesForAlteration = $this->cancelUsageGateway->getUsagesForAlteration($usageId);
        $this->validateUsages($usagesForAlteration);
        $this->resetActionsToAwardedPoints($usagesForAlteration);
        $this->recreateUsages($usagesForAlteration, $usageId);
        $this->cancelUsageGateway->cancelUsage($usageId);
        $this->cancelUsageGateway->commit();
    }

    /**
     * @param array $usagesForAlteration
     * @throws UsageNotFoundException
     */
    private function validateUsages(array $usagesForAlteration): void
    {
        if (!$usagesForAlteration) throw new UsageNotFoundException;
    }

    private function resetActionsToAwardedPoints(array $usagesForAlteration)
    {
        $affectedActionIds = $this->calculateAffectedActionsIds($usagesForAlteration);
        $this->actions = $this->cancelUsageGateway->getAffectedActions($affectedActionIds);
        $alteredActions = [];
        foreach ($this->actions as $action) {
            $action["activePoints"] = $action["awardedPoints"];
            $alteredActions[] = $action;
        }
        $this->actions = $alteredActions;
    }

    private function calculateAffectedActionsIds(array $usagesForAlteration): array
    {
        $usagesReductions = array_column($usagesForAlteration, "reductions");
        $reductions = array_merge(...$usagesReductions);
        return array_column($reductions, "actionId");
    }

    /**
     * @param array $usagesForAlteration
     * @param string $usageId
     * @throws Exception
     */
    private function recreateUsages(array $usagesForAlteration, string $usageId)
    {
        $this->setUsagesForRecreation($usagesForAlteration, $usageId);
        foreach ($this->usagesForRecreation as $usage) $this->recreateUsage($usage);
        $this->cancelUsageGateway->updateActions($this->actions);
        $this->cancelUsageGateway->updateUsages($this->usagesForRecreation);
    }

    /**
     * @param $usage
     * @throws Exception
     */
    private function recreateUsage($usage): void
    {
        $usageDate = new DateTime($usage["date"]);
        $usagePoints = array_sum(array_column($usage["reductions"], "usedPoints"));
        $activeActionsInDate = $this->getActiveActionsInDate($usageDate);
        $reductionResult = $this->reduceActions($activeActionsInDate, $usagePoints);
        $usage["reductions"] = $reductionResult["reductions"];
        $this->updateActions($reductionResult["actions"]);
        $this->updateUsages($usage);
    }

    /**
     * @param DateTime $usageDate
     * @return array
     * @throws Exception
     */
    private function getActiveActionsInDate(DateTime $usageDate): array
    {
        return array_filter($this->actions, function ($action) use ($usageDate) {
            $actionExpiryDate = new DateTime($action["expiryDate"]);
            $actionHasPoints = $action["activePoints"];
            return $actionExpiryDate >= $usageDate && $actionHasPoints;
        });
    }

    private function updateActions($actions)
    {
        $actionIds = array_column($actions, "id");
        $notAlteredActions = array_filter($this->actions, function ($action) use ($actionIds) {
            return !in_array($action["id"], $actionIds);
        });
        $this->actions = array_merge($notAlteredActions, $actions);
        usort($this->actions, function ($actionA, $actionB) {
            return $actionA["id"] <=> $actionB["id"];
        });
    }

    private function updateUsages(array $usage)
    {
        $usageId = $usage["id"];
        $notAlteredUsages = array_filter($this->usagesForRecreation, function ($usage) use ($usageId) {
            return $usage["id"] !== $usageId;
        });
        $this->usagesForRecreation = array_merge($notAlteredUsages, [$usage]);
        usort($this->usagesForRecreation, function ($usageA, $usageB) {
            return $usageA["id"] <=> $usageB["id"];
        });
    }

    private function setUsagesForRecreation(array $usagesForAlteration, string $usageId): void
    {
        $this->usagesForRecreation = array_filter($usagesForAlteration, function ($usage) use ($usageId) {
            return $usage["id"] !== $usageId;
        });
    }
}