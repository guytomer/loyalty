<?php


namespace Loyalty\Gateways;


use DateTime;
use Loyalty\Boundaries\AddPointsGatewayInterface;
use PDO;

class MySqlAddPointsGateway implements AddPointsGatewayInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addPoints(string $userId, int $points, DateTime $expiryDate)
    {
        $statement = $this->pdo->prepare("INSERT INTO actions (userId, activePoints, awardedPoints, expiryDate) VALUES (:userId, :activePoints, :awardedPoints, :expiryDate)");
        $statement->execute([
            "userId" => $userId,
            "activePoints" => $points,
            "awardedPoints" => $points,
            "expiryDate" => $expiryDate->format("Y-m-d H:i:s")
        ]);
    }
}