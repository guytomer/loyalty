<?php
require_once "autoloader.php";

use Loyalty\Tests\TestAddPoints;
use Loyalty\Tests\TestGetPointsBalance;


$testAddPoints = new TestAddPoints;
$testGetPointsBalance = new TestGetPointsBalance;
$tests = [
    "Add Negative Points" => [$testAddPoints, "testAddNegativePoints"],
    "Add Points" => [$testAddPoints, "testAddPoints"],
    "Get Zero Points Balance" => [$testGetPointsBalance, "testZeroBalance"],
    "Get Balance Of Non Expired Actions" => [$testGetPointsBalance, "testBalanceOfNonExpiredActions"],
    "Get Balance Without Expired Actions" => [$testGetPointsBalance, "testBalanceWithoutExpiredActions"],
    "Get Balance Without Non User Actions" => [$testGetPointsBalance, "testBalanceWithNonUserActions"],
];

foreach ($tests as $testName => $test) {
    $result = call_user_func($test) ? "Passed" : "Failed";
    echo "$testName: $result <BR>";
}