<?php


namespace Loyalty\Gateways;


use Loyalty\Boundaries\CancelUsageGatewayInterface;
use PDO;

class MySqlCancelUsageGateway implements CancelUsageGatewayInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->beginTransaction();
    }

    public function getAffectedActions(array $affectedActionIds): array
    {
        $in = str_repeat('?,', count($affectedActionIds) - 1) . '?';
        $query = "SELECT * FROM actions WHERE id IN ($in)";
        $statement = $this->pdo->prepare($query);
        $statement->execute($affectedActionIds);
        return $statement->fetchAll();
    }

    public function getUsagesForAlteration(string $usageId): array
    {
        $statement = $this->pdo->prepare("SELECT * FROM usages WHERE id = :usageId");
        $statement->execute(["usageId" => $usageId]);
        $usage = $statement->fetch();
        $statement = $this->pdo->prepare("SELECT * FROM usages WHERE id >= :usageId AND userId = :userId");
        $statement->execute(["userId" => $usage["userId"], "usageId" => $usageId]);
        $usages = $statement->fetchAll();
        foreach ($usages as &$usage) $usage["reductions"] = json_decode($usage["reductions"], true);
        return $usages;
    }

    public function updateActions(array $actions)
    {
        $statement = $this->pdo->prepare("UPDATE actions SET activePoints = :points WHERE id = :id");
        foreach ($actions as $action) $statement->execute([
            "id" => $action["id"],
            "points" => $action["activePoints"]
        ]);
    }

    public function updateUsages(array $usages)
    {
        $statement = $this->pdo->prepare("UPDATE usages SET reductions = :reductions WHERE id = :id");
        foreach ($usages as $usage) $statement->execute([
            "id" => $usage["id"],
            "reductions" => json_encode($usage["reductions"])
        ]);
    }

    public function cancelUsage(string $usageId)
    {
        $statement = $this->pdo->prepare("DELETE FROM usages WHERE id = :usageId");
        $statement->execute(["usageId" => $usageId]);
    }

    public function commit()
    {
        $this->pdo->commit();
    }
}