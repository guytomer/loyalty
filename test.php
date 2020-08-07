<?php
require_once "autoloader.php";

use Loyalty\Tests\TestAddPoints;


$testAddPoints = new TestAddPoints;
$tests = [
    "Add Negative Points" => [$testAddPoints, "testAddNegativePoints"],
    "Add Points" => [$testAddPoints, "testAddPoints"],
];

foreach ($tests as $testName => $test) {
    $result = call_user_func($test) ? "Passed" : "Failed";
    echo "Test $testName $result <BR>";
}