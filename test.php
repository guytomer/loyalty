<?php
require_once "autoloader.php";

use Loyalty\Tests\TestAddPoints;
use Loyalty\Tests\TestCancelUsage;
use Loyalty\Tests\TestGetPointsBalance;
use Loyalty\Tests\TestRefundPoints;
use Loyalty\Tests\TestUsePoints;


$testAddPoints = new TestAddPoints;
$testGetPointsBalance = new TestGetPointsBalance;
$testUsePoints = new TestUsePoints;
$testRefundPoints = new TestRefundPoints;
$testCancelUsage = new TestCancelUsage;
$tests = [
    "Add Negative Points" => $testAddPoints->testAddNegativePoints(),
    "Add Points" => $testAddPoints->testAddPoints(),
    "Get Zero Points Balance" => $testGetPointsBalance->testZeroBalance(),
    "Get Balance Of Non Expired Actions" => $testGetPointsBalance->testBalanceOfNonExpiredActions(),
    "Get Balance Without Expired Actions" => $testGetPointsBalance->testBalanceWithoutExpiredActions(),
    "Get Balance Without Non User Actions" => $testGetPointsBalance->testBalanceWithNonUserActions(),
    "Use Negative Points" => $testUsePoints->testUsingNegativePoints(),
    "Use Insufficient Points" => $testUsePoints->testUsingInsufficientPoints(),
    "Use Points" => $testUsePoints->testUsingPoints(),
    "Refund Negative Points" => $testRefundPoints->testRefundNegativePoints(),
    "Refund On Non Existing Usage" => $testRefundPoints->testRefundPointsWhenNoUsageFound(),
    "Refund Points Exceed Usage Points" => $testRefundPoints->testRefundPointsExceedUsagePoints(),
    "Refund Partial Points" => $testRefundPoints->testRefundPartialPoints(),
    "Refund Full Points" => $testRefundPoints->testRefundFullPoints(),
    "Cancel Non Existing Usage" => $testCancelUsage->testCancelNonExistingUsage(),
    "Cancel Usage" => $testCancelUsage->testCancelUsage(),
];

foreach ($tests as $testName => $testResult) {
    $passedOrFailed = $testResult ? "Passed" : "Failed";
    echo (!$testResult ? "<B>" : null) . "$testName: $passedOrFailed" . (!$testResult ? "</B>" : null) . "<BR>";
}