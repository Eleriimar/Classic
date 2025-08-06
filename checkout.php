<!DOCTYPE html>
<html lang="en">
<?php
include("connection/connect.php");
include("includes/send.mail.php");
include("includes/location_utils.php");
include("includes/mpesa_payment.php");
include("includes/order_assignment.php");
include_once 'product-action.php';
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/error.log'); // error log path
error_reporting(E_ALL);

session_start();


function function_alert($message) { 
    echo "<script>alert('$message');</script>"; 
    echo "<script>window.location.replace('your_orders.php');</script>"; 
} 

if(empty($_SESSION["user_id"]))
{
	header('location:login.php');
}
else{
    $item_total = 0;
    $delivery_fee = 0;
    $restaurant_id = 0;
    
    // Get user location and calculate delivery fee
    if(isset($_POST['latitude']) && isset($_POST['longitude'])) {
        $user_lat = floatval($_POST['latitude']);
        $user_lon = floatval($_POST['longitude']);
        $delivery_address = $_POST['delivery_address'] ?? '';
        
        // Save user location
        LocationUtils::saveUserLocation($db, $_SESSION["user_id"], $user_lat, $user_lon, $delivery_address);
        
        // Get delivery fee breakdown using new calculation method
        $fee_breakdown = LocationUtils::getDeliveryFeeBreakdown($db, $user_lat, $user_lon, 1);
        
        if(!isset($fee_breakdown['error'])) {
            $delivery_fee = $fee_breakdown['delivery_fee'];
            $delivery_zone = $fee_breakdown['zone'];
            $distance = $fee_breakdown['distance'];
        }
    }
    
    foreach ($_SESSION["cart_item"] as $item)
    {
        $item_total += ($item["price"]*$item["quantity"]);
        $restaurant_id = $item["restaurant_id"] ?? 1;
        
        if($_POST['submit'])
        {
            $user_lat = $_POST['latitude'] ?? 0;
            $user_lon = $_POST['longitude'] ?? 0;
            $delivery_address = $_POST['delivery_address'] ?? '';
            $payment_method = $_POST['payment_method'] ?? 'cash';
            $phone_number = $_POST['phone_number'] ?? '';
            
            // Insert order with delivery information
            $SQL = "INSERT INTO users_orders(u_id, title, quantity, price, delivery_fee, delivery_address, 
                    customer_latitude, customer_longitude, payment_method) 
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($SQL);
            $stmt->bind_param("isiidsss", 
                $_SESSION["user_id"], 
                $item["title"], 
                $item["quantity"], 
                $item["price"],
                $delivery_fee,
                $delivery_address,
                $user_lat,
                $user_lon,
                $payment_method
            );
            
            if($stmt->execute()) {
                $order_id = $db->insert_id;
                
                // Automatically assign order to available driver
                $orderAssignment = new OrderAssignment($db);
                $assignment_result = $orderAssignment->assignOrderToDriver($order_id);
                
                // Handle M-Pesa payment
                if($payment_method === 'mpesa' && !empty($phone_number)) {
                    $mpesa = new MpesaPayment($db);
                    $total_amount = $item["price"] * $item["quantity"] + $delivery_fee;
                    $payment_result = $mpesa->initiatePayment($phone_number, $total_amount, $order_id);
                    
                    if($payment_result['success']) {
                        $success = "Order placed! M-Pesa payment initiated. Check your phone for payment prompt.";
                    } else {
                        $success = "Order placed! Payment failed: " . $payment_result['message'];
                    }
                } else {
                    $success = "Thank you. Your order has been placed!";
                }
                
                // Add driver assignment message
                if($assignment_result['success']) {
                    $success .= " Your order has been assigned to driver: " . $assignment_result['driver_name'];
                    $success .= " (Estimated delivery: " . date('H:i', strtotime($assignment_result['estimated_delivery_time'])) . ")";
                    
                    // Notify driver
                    $orderAssignment->notifyDriver($assignment_result['driver_id'], $order_id);
                } else {
                    $success .= " " . $assignment_result['message'];
                }
                
                unset($_SESSION["cart_item"]);
                function_alert($success);
            } else {
                $error = "Failed to place order. Please try again.";
            }
        }
    }
?>


<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="#">
    <title>Checkout</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/animsition.min.css" rel="stylesheet">
    <link href="css/animate.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    
    <div class="site-wrapper">
        <header id="header" class="header-scroll top-header headrom">
            <nav class="navbar navbar-dark">
                <div class="container">
                    <button class="navbar-toggler hidden-lg-up" type="button" data-toggle="collapse" data-target="#mainNavbarCollapse">&#9776;</button>
                    <a class="navbar-brand" href="index.php"> <img class="img-rounded" src="images/icn.png" alt=""> </a>
                    <div class="collapse navbar-toggleable-md  float-lg-right" id="mainNavbarCollapse">
                        <ul class="nav navbar-nav">
                            <li class="nav-item"> <a class="nav-link active" href="index.php">Home <span class="sr-only">(current)</span></a> </li>
                            <li class="nav-item"> <a class="nav-link active" href="restaurants.php">Restaurants <span class="sr-only"></span></a> </li>
                            
							<?php
						if(empty($_SESSION["user_id"]))
							{
								echo '<li class="nav-item"><a href="login.php" class="nav-link active">Login</a> </li>
							  <li class="nav-item"><a href="registration.php" class="nav-link active">Register</a> </li>';
							}
						else
							{
									
									
										echo  '<li class="nav-item"><a href="your_orders.php" class="nav-link active">My Orders</a> </li>';
									echo  '<li class="nav-item"><a href="logout.php" class="nav-link active">Logout</a> </li>';
							}

						?>
							 
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        <div class="page-wrapper">
            <div class="top-links">
                <div class="container">
                    <ul class="row links">
                      
                        <li class="col-xs-12 col-sm-4 link-item"><span>1</span><a href="restaurants.php">Choose Restaurant</a></li>
                        <li class="col-xs-12 col-sm-4 link-item "><span>2</span><a href="#">Pick Your favorite food</a></li>
                        <li class="col-xs-12 col-sm-4 link-item active" ><span>3</span><a href="checkout.php">Order and Pay</a></li>
                    </ul>
                </div>
            </div>
			
                <div class="container">
                 
					   <span style="color:green;">
								<?php echo $success; ?>
										</span>
					
                </div>
            
			
			
				  
            <div class="container m-t-30">
			<form action="" method="post">
                <div class="widget clearfix">
                    
                    <div class="widget-body">
                        <form method="post" action="#">
                            <div class="row">
                                
                                <div class="col-sm-12">
                                    <div class="cart-totals margin-b-20">
                                        <div class="cart-totals-title">
                                            <h4>Cart Summary</h4> </div>
                                        <div class="cart-totals-fields">
										
                                            <table class="table">
											<tbody>
                                          
												 
											   
                                                    <tr>
                                                        <td>Cart Subtotal</td>
                                                        <td> <?php echo "ksh".$item_total; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Delivery Charges</td>
                                                        <td id="delivery-fee"> 
                                                            <?php echo "ksh".$delivery_fee; ?>
                                                            <?php if(isset($delivery_zone)): ?>
                                                                <br><small class="text-muted">Zone: <?php echo ucfirst($delivery_zone); ?></small>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-color"><strong>Total</strong></td>
                                                        <td class="text-color"><strong id="total-amount"> <?php echo "ksh".($item_total + $delivery_fee); ?></strong></td>
                                                    </tr>
                                                </tbody>
												
												
												
												
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <!-- Delivery Zones Info -->
                                    <div class="delivery-zones-info margin-b-20">
                                        <h4>Delivery Zones</h4>
                                        <div class="alert alert-info">
                                            <strong>Free Delivery:</strong> Within 5km radius<br>
                                            <strong>Standard Zone:</strong> 5-10km (Base rate)<br>
                                            <strong>Extended Zone:</strong> 10-15km (1.5x rate)<br>
                                            <strong>Premium Zone:</strong> 15km+ (2x rate)
                                        </div>
                                    </div>
                                    
                                    <!-- Location Section -->
                                    <div class="location-section margin-b-20">
                                        <h4>Delivery Location</h4>
                                        <div class="form-group">
                                            <label for="delivery_address">Delivery Address</label>
                                            <input type="text" class="form-control" id="delivery_address" name="delivery_address" placeholder="Enter your delivery address">
                                        </div>
                                        <div class="form-group">
                                            <button type="button" class="btn btn-primary" onclick="getCurrentLocation()">
                                                <i class="fa fa-map-marker"></i> Use Current Location
                                            </button>
                                            <span id="location-status"></span>
                                        </div>
                                        <input type="hidden" id="latitude" name="latitude" value="">
                                        <input type="hidden" id="longitude" name="longitude" value="">
                                    </div>
                                    
                                    <div class="payment-option">
                                        <h4>Payment Method</h4>
                                        <ul class=" list-unstyled">
                                            <li>
                                                <label class="custom-control custom-radio  m-b-20">
                                                    <input name="payment_method" id="radioStacked1" checked value="cash" type="radio" class="custom-control-input"> <span class="custom-control-indicator"></span> <span class="custom-control-description">Cash on Delivery</span>
                                                </label>
                                            </li>
                                            <li>
                                                <label class="custom-control custom-radio  m-b-10">
                                                    <input name="payment_method" type="radio" value="mpesa" class="custom-control-input"> <span class="custom-control-indicator"></span> <span class="custom-control-description">M-Pesa Mobile Money</span>
                                                </label>
                                            </li>
                                        </ul>
                                        
                                        <!-- M-Pesa Phone Number -->
                                        <div id="mpesa-section" style="display: none;">
                                            <div class="form-group">
                                                <label for="phone_number">M-Pesa Phone Number</label>
                                                <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="e.g., 0712345678">
                                            </div>
                                        </div>
                                        
                                        <p class="text-xs-center"> <input type="submit" onclick="return confirm('Do you want to confirm the order?');" name="submit"  class="btn btn-success btn-block" value="Order Now"> </p>
                                    </div>
									</form>
                                </div>
                            </div>
                       
                    </div>
                </div>
				 </form>
            </div>
            
            <script>
            // Location tracking functionality
            function getCurrentLocation() {
                if (navigator.geolocation) {
                    document.getElementById('location-status').innerHTML = 'Getting location...';
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            
                            document.getElementById('latitude').value = lat;
                            document.getElementById('longitude').value = lng;
                            document.getElementById('location-status').innerHTML = 'Location captured!';
                            
                            // Get address from coordinates
                            getAddressFromCoords(lat, lng);
                            
                            // Calculate delivery fee
                            calculateDeliveryFee(lat, lng);
                        },
                        function(error) {
                            document.getElementById('location-status').innerHTML = 'Error getting location: ' + error.message;
                        }
                    );
                } else {
                    document.getElementById('location-status').innerHTML = 'Geolocation is not supported by this browser.';
                }
            }
            
            function getAddressFromCoords(lat, lng) {
                const geocoder = new google.maps.Geocoder();
                const latlng = { lat: parseFloat(lat), lng: parseFloat(lng) };
                
                geocoder.geocode({ location: latlng }, function(results, status) {
                    if (status === 'OK') {
                        if (results[0]) {
                            document.getElementById('delivery_address').value = results[0].formatted_address;
                        }
                    }
                });
            }
            
            function calculateDeliveryFee(lat, lng) {
                // Send AJAX request to calculate delivery fee
                $.ajax({
                    url: 'calculate_delivery_fee.php',
                    method: 'POST',
                    data: {
                        latitude: lat,
                        longitude: lng
                    },
                    success: function(response) {
                        const data = JSON.parse(response);
                        let deliveryFeeHtml = 'ksh' + data.delivery_fee;
                        if (data.zone) {
                            deliveryFeeHtml += '<br><small class="text-muted">Zone: ' + data.zone.charAt(0).toUpperCase() + data.zone.slice(1) + '</small>';
                        }
                        if (data.is_free) {
                            deliveryFeeHtml = '<span class="text-success">FREE</span>';
                        }
                        document.getElementById('delivery-fee').innerHTML = deliveryFeeHtml;
                        document.getElementById('total-amount').innerHTML = 'ksh' + data.total_amount;
                    },
                    error: function() {
                        console.log('Error calculating delivery fee');
                    }
                });
            }
            
            // Payment method toggle
            $(document).ready(function() {
                $('input[name="payment_method"]').change(function() {
                    if ($(this).val() === 'mpesa') {
                        $('#mpesa-section').show();
                    } else {
                        $('#mpesa-section').hide();
                    }
                });
            });
            </script>
            
            <footer class="footer">
                    <div class="row bottom-footer">
                        <div class="container">
                            <div class="row">
                                <div class="col-xs-12 col-sm-3 payment-options color-gray">
                                    <h5>Payment Options</h5>
                                    <ul>
                                        <li>
                                            <a href="#"> <img src="images/paypal.png" alt="Paypal"> </a>
                                        </li>
                                        <li>
                                            <a href="#"> <img src="images/mastercard.png" alt="Mastercard"> </a>
                                        </li>
                                        <li>
                                            <a href="#"> <img src="images/maestro.png" alt="Maestro"> </a>
                                        </li>
                                        <li>
                                            <a href="#"> <img src="images/stripe.png" alt="Stripe"> </a>
                                        </li>
                                        <li>
                                            <a href="#"> <img src="images/bitcoin.png" alt="Bitcoin"> </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-xs-12 col-sm-4 address color-gray">
                                    <h5>Address</h5>
                                    <p> Outering Road</p>
                                    <h5>Phone: +254 768343346</a></h5> </div>
                                <div class="col-xs-12 col-sm-5 additional-info color-gray">
                                    <h5>Addition informations</h5>
                                   <p>Join thousands of other restaurants who benefit from having partnered with us.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
         </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/tether.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/animsition.min.js"></script>
    <script src="js/bootstrap-slider.min.js"></script>
    <script src="js/jquery.isotope.min.js"></script>
    <script src="js/headroom.js"></script>
    <script src="js/foodpicky.min.js"></script>
</body>

</html>

<?php
}
?>
