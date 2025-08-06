<?php
// Simple test to check checkout page functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing Checkout Page Components</h1>";

// Test 1: Database Connection
echo "<h2>1. Testing Database Connection</h2>";
try {
    include("connection/connect.php");
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Test 2: Location Utils
echo "<h2>2. Testing Location Utils</h2>";
try {
    include("includes/location_utils.php");
    echo "✅ Location utils loaded successfully<br>";
    
    // Test distance calculation
    $distance = LocationUtils::calculateDistance(0, 0, 0.1, 0.1);
    echo "✅ Distance calculation works: " . round($distance, 2) . " km<br>";
} catch (Exception $e) {
    echo "❌ Location utils failed: " . $e->getMessage() . "<br>";
}

// Test 3: Order Assignment
echo "<h2>3. Testing Order Assignment</h2>";
try {
    include("includes/order_assignment.php");
    echo "✅ Order assignment loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Order assignment failed: " . $e->getMessage() . "<br>";
}

// Test 4: M-Pesa Payment
echo "<h2>4. Testing M-Pesa Payment</h2>";
try {
    include("includes/mpesa_payment.php");
    echo "✅ M-Pesa payment loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ M-Pesa payment failed: " . $e->getMessage() . "<br>";
}

// Test 5: Send Mail
echo "<h2>5. Testing Send Mail</h2>";
try {
    include("includes/send.mail.php");
    echo "✅ Send mail loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Send mail failed: " . $e->getMessage() . "<br>";
}

// Test 6: Product Action
echo "<h2>6. Testing Product Action</h2>";
try {
    include("product-action.php");
    echo "✅ Product action loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Product action failed: " . $e->getMessage() . "<br>";
}

echo "<h2>Test Complete</h2>";
echo "<p>If all tests pass, the checkout page should work. If any fail, those need to be fixed.</p>";
?> 