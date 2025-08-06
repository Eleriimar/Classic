<?php
include("../connection/connect.php");
include("../includes/location_utils.php");

session_start();

// Check if driver is logged in
if (!isset($_SESSION['driver_id'])) {
    header('location:login.php');
    exit();
}

$driver_id = $_SESSION['driver_id'];

// Get driver information
$driver_query = "SELECT * FROM drivers WHERE id = ?";
$driver_stmt = $db->prepare($driver_query);
$driver_stmt->bind_param("i", $driver_id);
$driver_stmt->execute();
$driver = $driver_stmt->get_result()->fetch_assoc();

// Get assigned orders
$orders_query = "SELECT uo.*, dt.status as delivery_status, dt.driver_latitude, dt.driver_longitude,
                        u.username as customer_name, u.phone as customer_phone
                 FROM users_orders uo 
                 LEFT JOIN delivery_tracking dt ON uo.o_id = dt.order_id
                 LEFT JOIN users u ON uo.u_id = u.u_id
                 WHERE dt.driver_id = ? AND dt.status != 'delivered'
                 ORDER BY dt.created_at DESC";
$orders_stmt = $db->prepare($orders_query);
$orders_stmt->bind_param("i", $driver_id);
$orders_stmt->execute();
$orders = $orders_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Driver Dashboard</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="site-wrapper">
        <header id="header" class="header-scroll top-header headrom">
            <nav class="navbar navbar-dark">
                <div class="container">
                    <a class="navbar-brand" href="dashboard.php">Driver Dashboard</a>
                    <div class="float-lg-right">
                        <span class="text-white">Welcome, <?php echo $driver['name']; ?></span>
                        <a href="logout.php" class="btn btn-outline-light ml-2">Logout</a>
                    </div>
                </div>
            </nav>
        </header>

        <div class="page-wrapper">
            <div class="container m-t-30">
                <div class="row">
                    <div class="col-md-8">
                        <div class="widget clearfix">
                            <div class="widget-body">
                                <h4>My Assigned Orders</h4>
                                <?php if ($orders->num_rows > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Customer</th>
                                                    <th>Item</th>
                                                    <th>Address</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($order = $orders->fetch_assoc()): ?>
                                                    <tr data-order-id="<?php echo $order['o_id']; ?>">
                                                        <td>#<?php echo $order['o_id']; ?></td>
                                                        <td>
                                                            <?php echo $order['customer_name']; ?><br>
                                                            <small><?php echo $order['customer_phone']; ?></small>
                                                        </td>
                                                        <td><?php echo $order['title']; ?></td>
                                                        <td><?php echo $order['delivery_address']; ?></td>
                                                        <td>
                                                            <span class="badge badge-<?php echo getStatusBadge($order['delivery_status']); ?>">
                                                                <?php echo ucfirst(str_replace('_', ' ', $order['delivery_status'])); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-primary btn-sm" onclick="viewOrderDetails(<?php echo $order['o_id']; ?>)">
                                                                View Details
                                                            </button>
                                                            <button class="btn btn-success btn-sm" onclick="updateStatus(<?php echo $order['o_id']; ?>)">
                                                                Update Status
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-center">No orders assigned yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="widget clearfix">
                            <div class="widget-body">
                                <h4>My Location</h4>
                                <div id="driver-map" style="height: 300px; width: 100%;"></div>
                                <div class="mt-3">
                                    <button class="btn btn-primary" onclick="updateMyLocation()">
                                        Update My Location
                                    </button>
                                </div>
                                <div id="location-status" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="order-details"></div>
                    <div id="delivery-map" style="height: 300px; width: 100%; margin-top: 20px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Delivery Status</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="status-form">
                        <input type="hidden" id="order-id" name="order_id">
                        <div class="form-group">
                            <label>New Status</label>
                            <select class="form-control" name="status" id="status-select">
                                <option value="picked_up">Picked Up</option>
                                <option value="in_transit">In Transit</option>
                                <option value="delivered">Delivered</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Notes (Optional)</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitStatusUpdate()">Update Status</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    let driverMap, deliveryMap;
    
    // Initialize driver location map
    function initDriverMap() {
        driverMap = new google.maps.Map(document.getElementById('driver-map'), {
            zoom: 15,
            center: { lat: -1.2921, lng: 36.8219 } // Default to Nairobi
        });
        
        // Try to get current location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    driverMap.setCenter(pos);
                    new google.maps.Marker({
                        position: pos,
                        map: driverMap,
                        title: 'Your Location'
                    });
                    
                    // Update location in database
                    updateDriverLocation(position.coords.latitude, position.coords.longitude);
                },
                function() {
                    console.log('Error getting location');
                }
            );
        }
    }
    
    function updateMyLocation() {
        if (navigator.geolocation) {
            document.getElementById('location-status').innerHTML = 'Getting location...';
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    // Update map
                    const pos = { lat: lat, lng: lng };
                    driverMap.setCenter(pos);
                    driverMap.setZoom(15);
                    
                    // Clear existing markers
                    driverMap.setMap(null);
                    new google.maps.Marker({
                        position: pos,
                        map: driverMap,
                        title: 'Your Location'
                    });
                    
                    // Update database
                    updateDriverLocation(lat, lng);
                    document.getElementById('location-status').innerHTML = 'Location updated!';
                },
                function(error) {
                    document.getElementById('location-status').innerHTML = 'Error: ' + error.message;
                }
            );
        }
    }
    
    function updateDriverLocation(lat, lng) {
        $.ajax({
            url: 'update_driver_location.php',
            method: 'POST',
            data: {
                latitude: lat,
                longitude: lng
            },
            success: function(response) {
                console.log('Location updated');
            }
        });
    }
    
    function viewOrderDetails(orderId) {
        $.ajax({
            url: 'get_order_details.php',
            method: 'POST',
            data: { order_id: orderId },
            success: function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    displayOrderDetails(data);
                    $('#orderModal').modal('show');
                }
            }
        });
    }
    
    function displayOrderDetails(data) {
        $('#order-details').html(`
            <div class="row">
                <div class="col-md-6">
                    <h6>Order Information</h6>
                    <p><strong>Order ID:</strong> #${data.order_id}</p>
                    <p><strong>Item:</strong> ${data.item_title}</p>
                    <p><strong>Quantity:</strong> ${data.quantity}</p>
                    <p><strong>Total:</strong> KES ${data.price}</p>
                </div>
                <div class="col-md-6">
                    <h6>Customer Information</h6>
                    <p><strong>Name:</strong> ${data.customer_name}</p>
                    <p><strong>Phone:</strong> ${data.customer_phone}</p>
                    <p><strong>Address:</strong> ${data.delivery_address}</p>
                </div>
            </div>
        `);
        
        // Initialize delivery map
        if (data.customer_latitude && data.customer_longitude) {
            deliveryMap = new google.maps.Map(document.getElementById('delivery-map'), {
                zoom: 15,
                center: { lat: parseFloat(data.customer_latitude), lng: parseFloat(data.customer_longitude) }
            });
            
            // Add customer location marker
            new google.maps.Marker({
                position: { lat: parseFloat(data.customer_latitude), lng: parseFloat(data.customer_longitude) },
                map: deliveryMap,
                title: 'Delivery Location',
                icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
            });
        }
    }
    
    function updateStatus(orderId) {
        $('#order-id').val(orderId);
        $('#statusModal').modal('show');
    }
    
    function submitStatusUpdate() {
        const formData = new FormData(document.getElementById('status-form'));
        
        $.ajax({
            url: 'update_delivery_status.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    $('#statusModal').modal('hide');
                    location.reload(); // Refresh to show updated status
                } else {
                    alert('Error updating status: ' + data.message);
                }
            }
        });
    }
    
    // Initialize map when page loads
    $(document).ready(function() {
        initDriverMap();
        
        // Auto-refresh orders every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    });
    
    function getStatusBadge(status) {
        switch(status) {
            case 'assigned': return 'info';
            case 'picked_up': return 'warning';
            case 'in_transit': return 'primary';
            case 'delivered': return 'success';
            default: return 'secondary';
        }
    }
    </script>
</body>
</html>

<?php
function getStatusBadge($status) {
    switch($status) {
        case 'assigned': return 'info';
        case 'picked_up': return 'warning';
        case 'in_transit': return 'primary';
        case 'delivered': return 'success';
        default: return 'secondary';
    }
}
?> 