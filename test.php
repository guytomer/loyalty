<?php
require_once "autoloader.php";

use Loyalty\Tests\TestAddPoints;
use Loyalty\Tests\TestGetPointsBalance;
use Loyalty\Tests\TestUsePoints;
use Loyalty\Tests\TestRefundPoints;


$testAddPoints = new TestAddPoints;
$testGetPointsBalance = new TestGetPointsBalance;
$testUsePoints = new TestUsePoints;
$testRefundPoints = new TestRefundPoints;
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
    "Refund Negative Points" => [$testRefundPoints, "testRefundNegativePoints"],
    "Refund On Non Existing Usage" => [$testRefundPoints, "testRefundPointsWhenNoUsageFound"],
    "Refund Points Exceed Usage Points" => [$testRefundPoints, "testRefundPointsExceedUsagePoints"],
    "Refund Partial Points" => [$testRefundPoints, "testRefundPartialPoints"],
    "Refund Full Points" => [$testRefundPoints, "testRefundFullPoints"],
];

foreach ($tests as $testName => $test) {
    $passed = call_user_func($test);
    $passedOrFailed = $passed ? "Passed" : "Failed";
    echo (!$passed ? "<B>" : null) . "$testName: $passedOrFailed" . (!$passed ? "</B>" : null) ."<BR>";
}