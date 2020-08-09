<?php


namespace Loyalty\Gateways;


use DateTime;
use Loyalty\Boundaries\UsePointsGatewayInterface;
use PDO;

class MySqlUsePointsGateway implements UsePointsGatewayInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->beginTransaction();
    }

    public function createPointsUsage(string $userId, array $reductions, DateTime $date): string
    {
        $statement = $this->pdo->prepare("INSERT INTO usages (userId, reductions, date) VALUES (:userId, :reductions, :date)");
        $statement->execute(["userId" => $userId, "reductions" => json_encode($reductions), "date" => $date->format("Y-m-d H:i:s")]);
        return $this->pdo->lastInsertId();
    }

    public function updateActions(array $actions)
    {
        $statement = $this->pdo->prepare("UPDATE actions SET activePoints = :points WHERE id = :id");
        foreach ($actions as $action) $statement->execute([
            "id" => $action["id"],
            "points" => $action["activePoints"]
        ]);
    }

    public function __destruct()
    {
        $this->pdo->commit();
    }
}