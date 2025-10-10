<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Dashboard API Test</h1>";
echo "<pre>";

// Start session
session_start();
$_SESSION['tenant_id'] = 'DEMO01';
$_SESSION['member_id'] = 'M001';
$_SESSION['role'] = 'admin';
$_SESSION['token'] = bin2hex(random_bytes(16));

// Simulate API call
$_GET['start_date'] = '2025-01-01';
$_GET['end_date'] = '2025-01-31';
$_GET['granularity'] = 'monthly';

echo "Session set:\n";
print_r($_SESSION);
echo "\n\nGET parameters:\n";
print_r($_GET);
echo "\n\nCalling API...\n\n";

// Capture output
ob_start();
try {
    include __DIR__ . '/api/dashboard.php';
    $output = ob_get_clean();

    echo "API Response:\n";
    $json = json_decode($output, true);
    if ($json) {
        print_r($json);
    } else {
        echo "Raw output:\n";
        echo $output;
    }
} catch (Exception $e) {
    ob_end_clean();
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
}

echo "</pre>";
