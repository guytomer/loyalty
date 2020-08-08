<?php
require_once "autoloader.php";

use Loyalty\Exceptions\InsufficientPointsException;
use Loyalty\Exceptions\InvalidPointsException;
use Loyalty\Exceptions\UsageNotFoundException;
use Loyalty\Gateways\MySqlActionsGateway;
use Loyalty\Gateways\MySqlAddPointsGateway;
use Loyalty\Gateways\MySqlCancelUsageGateway;
use Loyalty\Gateways\MySqlUsePointsGateway;
use Loyalty\UseCases\AddPoints;
use Loyalty\UseCases\CancelUsage;
use Loyalty\UseCases\Clock;
use Loyalty\UseCases\GetActiveActions;
use Loyalty\UseCases\GetPointsBalance;
use Loyalty\UseCases\UsePoints;

$hostname = "localhost";
$username = "root";
$password = "";
$pdo = new PDO("mysql:host=$hostname;dbname=loyalty", $username, $password);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

clearDatabase($pdo);
addPointsToUser($pdo, "1", 50, new DateTime("2020-01-05"));
addPointsToUser($pdo, "1", 30, new DateTime("2020-01-07"));
displayUserBalance($pdo, new DateTime("2020-01-05"));
$usageOne = usePoints($pdo, "1", 30, new DateTime("2020-01-05"));
$usageTwo = usePoints($pdo, "1", 20, new DateTime("2020-01-05"));
displayUserBalance($pdo, new DateTime("2020-01-05"));
cancelUsage($pdo, $usageOne);
displayUserBalance($pdo, new DateTime("2020-01-05"));

function clearDatabase(PDO $pdo)
{
    $pdo->query("TRUNCATE actions");
    $pdo->query("TRUNCATE usages");
}

function addPointsToUser(PDO $pdo, string $userId, int $points, DateTime $date): void
{
    $addPointsGateway = new MySqlAddPointsGateway($pdo);
    $addPoints = new AddPoints($addPointsGateway);
    try {
        $addPoints->execute($userId, $points, $date);
    } catch (InvalidPointsException $e) {
    }
}

function displayUserBalance(PDO $pdo, DateTime $date): void
{
    $actionsGateway = new MySqlActionsGateway($pdo);
    $getActiveActions = new GetActiveActions($actionsGateway);
    $getPointsBalance = new GetPointsBalance($getActiveActions);
    try {
        $balance = $getPointsBalance->execute("1", $date);
        echo "User has $balance points.<BR>";
    } catch (Exception $e) {
    }
}

function usePoints(PDO $pdo, string $userId, int $points, DateTime $date)
{
    $actionsGateway = new MySqlActionsGateway($pdo);
    $usePointsGateway = new MySqlUsePointsGateway($pdo);
    $getActiveActions = new GetActiveActions($actionsGateway);
    $clock = new Clock($date);
    $usePoints = new UsePoints($usePointsGateway, $getActiveActions, $clock);
    try {
        $usageId = $usePoints->execute($userId, $points);
        echo "User used $points points. (Usage $usageId)<BR>";
        return $usageId;
    } catch (InsufficientPointsException $e) {
        $pdo->rollBack();
    } catch (InvalidPointsException $e) {
        $pdo->rollBack();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
    return null;
}

function cancelUsage(PDO $pdo, string $usageId)
{
    $cancelUsageGateway = new MySqlCancelUsageGateway($pdo);
    $cancelUsage = new CancelUsage($cancelUsageGateway);
    try {
        $cancelUsage->execute($usageId);
        echo "Canceled usage $usageId.<BR>";
    } catch (UsageNotFoundException $e) {
    } catch (Exception $e) {
    }
}