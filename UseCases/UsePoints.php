<?php


namespace Loyalty\UseCases;


use Exception;
use Loyalty\Boundaries\UsePointsGatewayInterface;
use Loyalty\Exceptions\InsufficientPointsException;
use Loyalty\Exceptions\InvalidPointsException;

class UsePoints
{
    use ActionReducer;

    private UsePointsGatewayInterface $usePointsGateway;
    private Clock $clock;
    private GetActiveActions $getActiveActions;

    public function __construct(UsePointsGatewayInterface $usePointsGateway,
                                GetActiveActions $getActiveActions,
                                Clock $clock)
    {
        $this->usePointsGateway = $usePointsGateway;
        $this->getActiveActions = $getActiveActions;
        $this->clock = $clock;
    }

    /**
     * @param string $userId
     * @param int $points
     * @return string
     * @throws InvalidPointsException|InsufficientPointsException
     * @throws Exception
     */
    public function execute(string $userId, int $points): string
    {
        $this->validateUsedPoints($points);
        $this->validateUserHasEnoughPoints($userId, $points);
        return $this->createUsage($userId, $points);
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
        $getPointsBalance = new GetPointsBalance($this->getActiveActions);
        $currentUserPoints = $getPointsBalance->execute($userId, $this->clock->now());
        if ($points > $currentUserPoints) throw new InsufficientPointsException;
    }

    /**
     * @param string $userId
     * @param int $points
     * @return string
     * @throws Exception
     */
    private function createUsage(string $userId, int $points): string
    {
        $actions = $this->getActiveActions->execute($userId, $this->clock->now());
        $reductionResult = $this->reduceActions($actions, $points);
        $usageId = $this->usePointsGateway->createPointsUsage($userId, $reductionResult["reductions"], $this->clock->now());
        $this->usePointsGateway->updateActions($reductionResult["actions"]);
        return $usageId;
    }
}