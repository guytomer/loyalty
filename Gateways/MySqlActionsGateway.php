<?php


namespace Loyalty\Gateways;

use DateTime;
use Loyalty\Boundaries\ActionsGatewayInterface;
use PDO;

class MySqlActionsGateway implements ActionsGatewayInterface
{

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getActionsForUserSinceDate(string $userId, DateTime $requestedDate): array
    {
        $statement = $this->pdo->prepare("SELECT * FROM actions WHERE userId = :userId AND expiryDate >= :requestedDate");
        $statement->execute(["userId" => $userId, "requestedDate" => $requestedDate->format("Y-m-d H:i:s")]);
        return $statement->fetchAll();
    }
}