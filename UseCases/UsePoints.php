<?php


namespace Loyalty\UseCases;


use Exception;
use Loyalty\Boundaries\UsePointsGatewayInterface;
use Loyalty\Exceptions\InsufficientPointsException;
use Loyalty\Exceptions\InvalidPointsException;

class UsePoints
{
    private UsePointsGatewayInterface $usePointsGateway;
    private Clock $clock;
    private GetActivePoints $getActivePoints;

    public function __construct(UsePointsGatewayInterface $usePointsGateway,
                                GetActivePoints $getActivePoints,
                                Clock $clock)
    {
        $this->usePointsGateway = $usePointsGateway;
        $this->getActivePoints = $getActivePoints;
        $this->clock = $clock;
    }

    /**
     * @param string $userId
     * @param int $points
     * @throws InvalidPointsException|InsufficientPointsException
     * @throws Exception
     */
    public function execute(string $userId, int $points) {
        $this->validateUsedPoints($points);
        $this->validateUserHasEnoughPoints($userId, $points);
        $this->createUsage($userId, $points);
    }

    /**
     * @param int $points
     * @throws InvalidPointsException
     */
    private function validateUsedPoints(int $points)
    {
        if ($points < 0) throw new InvalidPointsException;
    }

    /**
     * @param string $userId
     * @param int $points
     * @throws InsufficientPointsException
     * @throws Exception
     */
    private function validateUserHasEnoughPoints(string $userId, int $points)
    {
        $getPointsBalance = new GetPointsBalance($this->getActivePoints);
        $currentUserPoints = $getPointsBalance->execute($userId, $this->clock->now());
        if ($points > $currentUserPoints) throw new InsufficientPointsException;
    }

    /**
     * @param string $userId
     * @param int $points
     * @throws Exception
     */
    private function createUsage(string $userId, int $points)
    {
        $actions = $this->getActivePoints->execute($userId, $this->clock->now());
        $reductionResult = $this->reduceActions($actions, $points);
        $this->usePointsGateway->createPointsUsage($reductionResult["reductions"]);
        $this->usePointsGateway->updateActions($reductionResult["actions"]);
        $this->usePointsGateway->commit();
    }

    private function reduceActions(array $actions, int $points): array
    {
        return array_reduce($actions, function ($currentState, $action) {
            $points = $currentState["points"];
            $reductions = $currentState["reductions"];
            $actions = $currentState["actions"];
            $actionReduction = $points >= $action["activePoints"] ? $action["activePoints"] : $points;
            if ($actionReduction) {
                $action["activePoints"] -= $actionReduction;
                $reductions[] = ["id" => $action["id"], "usedPoints" => $actionReduction];
                $actions[] = $action;
            }
            return [
                "points" => $points - $actionReduction,
                "reductions" => $reductions,
                "actions" => $actions
            ];
        }, ["points" => $points, "reductions" => [], "actions" => []]);
    }
}