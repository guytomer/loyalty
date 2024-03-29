<?php


namespace Loyalty\UseCases;


use DateTime;
use Exception;
use Loyalty\Boundaries\RefundPointsGatewayInterface;
use Loyalty\Exceptions\InvalidPointsException;
use Loyalty\Exceptions\UsageNotFoundException;

class RefundPoints
{
    private RefundPointsGatewayInterface $refundPointsGateway;
    private array $usageReductions;
    private array $affectedActions;

    public function __construct(RefundPointsGatewayInterface $refundPointsGateway)
    {
        $this->refundPointsGateway = $refundPointsGateway;
    }

    /**
     * @param string $usageId
     * @param int $points
     * @throws InvalidPointsException
     * @throws UsageNotFoundException
     * @throws Exception
     */
    public function execute(string $usageId, int $points) {
        $this->validateRefundedPoints($points);
        $this->fetchAffectedActions($usageId);
        $this->fetchUsageReductions($usageId);
        $this->validateUsageCanBeRefundedWithPoints($points);
        $this->refundPoints($usageId, $points);
    }

    /**
     * @param int $points
     * @throws InvalidPointsException
     */
    private function validateRefundedPoints(int $points)
    {
        if ($points < 0) throw new InvalidPointsException;
    }

    /**
     * @param int $points
     * @throws InvalidPointsException
     */
    private function validateUsageCanBeRefundedWithPoints(int $points)
    {
        $usageReductionPoints = array_column($this->usageReductions, "usedPoints");
        $reductionPoints = array_sum($usageReductionPoints);
        if ($points > $reductionPoints) throw new InvalidPointsException;
    }

    private function refundPoints(string $usageId, int $points)
    {
        $this->refundPointsGateway->createRefund($usageId, $points);
        $refundResult = $this->calculateRefund($points);
        $this->refundPointsGateway->updateUsage($usageId, $refundResult["reductions"]);
        $this->refundPointsGateway->updateActions($refundResult["actions"]);
    }

    /**
     * @param string $usageId
     * @throws Exception
     */
    private function fetchUsageReductions(string $usageId)
    {
        $this->usageReductions = $this->refundPointsGateway->getUsageReductions($usageId);
        $affectedActions = $this->affectedActions;
        usort($this->usageReductions, function ($reductionA, $reductionB) use ($affectedActions) {
            $actionAId = $reductionA["actionId"];
            $actionBId = $reductionB["actionId"];
            $actionA = current(array_filter($affectedActions, function ($action) use ($actionAId) {
                return $action["id"] === $actionAId;
            }));
            $actionB = current(array_filter($affectedActions, function ($action) use ($actionBId) {
                return $action["id"] === $actionBId;
            }));
            if ($actionA && $actionB) {
                $actionAExpiry = new DateTime($actionA["expiryDate"]);
                $actionBExpiry = new DateTime($actionB["expiryDate"]);
                return $actionBExpiry <=> $actionAExpiry;
            }
            return $actionBId <=> $actionAId;
        });
    }

    /**
     * @param string $usageId
     * @throws UsageNotFoundException
     */
    private function fetchAffectedActions(string $usageId)
    {
        $this->affectedActions = $this->refundPointsGateway->getAffectedActions($usageId);
        if (!$this->affectedActions) throw new UsageNotFoundException;
    }

    private function calculateRefund(int $points): array
    {
        $affectedActions = $this->affectedActions;
        return array_reduce($this->usageReductions, function ($currentState, $reduction) use ($affectedActions) {
            $points = $currentState["points"];
            $reductions = $currentState["reductions"];
            $actions = $currentState["actions"];
            $actionIncrease = $points >= $reduction["usedPoints"] ? $reduction["usedPoints"] : $points;
            $actionId = $reduction["actionId"];
            $action = current(array_filter($affectedActions, function ($action) use ($actionId) {
                return $action["id"] === $actionId;
            }));
            if ($actionIncrease) {
                $action["activePoints"] += $actionIncrease;
                $alteredUsedPoints = $reduction["usedPoints"] - $actionIncrease;
                if ($alteredUsedPoints) $reductions[] = ["id" => $action["id"], "usedPoints" => $alteredUsedPoints];
                $actions[] = $action;
            }
            return [
                "points" => $points - $actionIncrease,
                "reductions" => $reductions,
                "actions" => $actions
            ];
        }, ["points" => $points, "reductions" => [], "actions" => []]);
    }
}