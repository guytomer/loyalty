<?php
require_once "autoloader.php";

use Loyalty\Tests\TestAddPoints;
use Loyalty\Tests\TestGetPointsBalance;
use Loyalty\Tests\TestUsePoints;


$testAddPoints = new TestAddPoints;
$testGetPointsBalance = new TestGetPointsBalance;
$testUsePoints = new TestUsePoints;
$tests = [
    "Add Negative Points" => [$testAddPoints, "testAddNegativePoints"],
    "Add Points" => [$testAddPoints, "testAddPoints"],
    "Get Zero Points Balance" => [$testGetPointsBalance, "testZeroBalance"],
    "Get Balance Of Non Expired Actions" => [$testGetPointsBalance, "testBalanceOfNonExpiredActions"],
    "Get Balance Without Expired Actions" => [$testGetPointsBalance, "testBalanceWithoutExpiredActions"],
    "Get Balance Without Non User Actions" => [$testGetPointsBalance, "testBalanceWithNonUserActions"],
    "Use Negative Points" => [$testUsePoints, "testUsingNegativePoints"],
    "Use Insufficient Points" => [$testUsePoints, "testUsingInsufficientPoints"],
    "Use Points" => [$testUsePoints, "testUsingPoints"],
];

foreach ($tests as $testName => $test) {
    $result = call_user_func($test) ? "Passed" : "Failed";
    echo "$testName: $result <BR>";
}