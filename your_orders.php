<!DOCTYPE html>
<html lang="en">
<?php
include("connection/connect.php");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/error.log'); // error log path
error_reporting(E_ALL);

session_start();

if(!isset($_SESSION['user_id'])) {
    header('location:login.php');
} else {
    session_regenerate_id(true);
?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="#">
    <title>My Orders</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/animsition.min.css" rel="stylesheet">
    <link href="css/animate.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style type="text/css" rel="stylesheet">
        .indent-small {
            margin-left: 5px;
        }
        .form-group.internal {
            margin-bottom: 0;
        }
        .dialog-panel {
            margin: 10px;
        }
        .datepicker-dropdown {
            z-index: 200 !important;
        }
        .panel-body {
            background: #e5e5e5;
            background: -moz-radial-gradient(center, ellipse cover, #e5e5e5 0%, #ffffff 100%);
            background: -webkit-gradient(radial, center center, 0px, center center, 100%, color-stop(0%, #e5e5e5), color-stop(100%, #ffffff));
            background: -webkit-radial-gradient(center, ellipse cover, #e5e5e5 0%, #ffffff 100%);
            background: -o-radial-gradient(center, ellipse cover, #e5e5e5 0%, #ffffff 100%);
            background: -ms-radial-gradient(center, ellipse cover, #e5e5e5 0%, #ffffff 100%);
            background: radial-gradient(ellipse at center, #e5e5e5 0%, #ffffff 100%);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#e5e5e5', endColorstr='#ffffff', GradientType=1);
            font: 600 15px "Open Sans", Arial, sans-serif;
        }
        label.control-label {
            font-weight: 600;
            color: #777;
        }

        @media only screen and (max-width: 768px),
               only screen and (min-device-width: 768px) and (max-device-width: 1024px) {
            /* Responsive styles would go here */
        }
    </style>
</head>

<body>
    <header id="header" class="header-scroll top-header headrom">
        <nav class="navbar navbar-dark">
            <div class="container">
                <button class="navbar-toggler hidden-lg-up" type="button" data-toggle="collapse" data-target="#mainNavbarCollapse">&#9776;</button>
                <a class="navbar-brand" href="index.php"><img class="img-rounded" src="images/icn.png" alt=""></a>
                <div class="collapse navbar-toggleable-md float-lg-right" id="mainNavbarCollapse">
                    <ul class="nav navbar-nav">
                        <li class="nav-item"><a class="nav-link active" href="index.php">Home <span class="sr-only">(current)</span></a></li>
                        <li class="nav-item"><a class="nav-link active" href="restaurants.php">Restaurants</a></li>
                        <?php
                        if(empty($_SESSION["user_id"])) {
                            echo '<li class="nav-item"><a href="login.php" class="nav-link active">Login</a></li>
                                  <li class="nav-item"><a href="registration.php" class="nav-link active">Register</a></li>';
                        } else {
                            echo '<li class="nav-item"><a href="your_orders.php" class="nav-link active">My Orders</a></li>
                                  <li class="nav-item"><a href="logout.php" class="nav-link active">Logout</a></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="page-wrapper">
        <div class="inner-page-hero bg-image" data-image-src="images/img/pimg.jpg">
            <div class="container"></div>
        </div>
        
        <div class="result-show">
            <div class="container">
                <div class="row"></div>
            </div>
        </div>

        <section class="restaurants-page">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="bg-gray">
                            <div class="row">
                                <table class="table table-bordered table-hover">
                                    <thead style="background: #404040; color:white;">
                                        <tr>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $query_res = mysqli_query($db, "select * from users_orders where u_id='".$_SESSION['user_id']."'");
                                        if(!mysqli_num_rows($query_res) > 0) {
                                            echo '<tr><td colspan="6"><center>You have No orders Placed yet.</center></td></tr>';
                                        } else {
                                            while($row = mysqli_fetch_array($query_res)) {
                                        ?>
                                                <tr>    
                                                    <td data-column="Item"><?php echo $row['title']; ?></td>
                                                    <td data-column="Quantity"><?php echo $row['quantity']; ?></td>
                                                    <td data-column="price">ksh<?php echo $row['price']; ?></td>
                                                    <td data-column="status">
                                                        <?php 
                                                        $status = $row['status'];
                                                        if($status == "" || $status == "NULL") {
                                                        ?>
                                                            <button type="button" class="btn btn-info"><span class="fa fa-bars" aria-hidden="true"></span> Dispatch</button>
                                                        <?php 
                                                        } elseif($status == "in process") { 
                                                        ?>
                                                            <button type="button" class="btn btn-warning"><span class="fa fa-cog fa-spin" aria-hidden="true"></span> On The Way!</button>
                                                        <?php
                                                        } elseif($status == "closed") {
                                                        ?>
                                                            <button type="button" class="btn btn-success"><span class="fa fa-check-circle" aria-hidden="true"></span> Delivered</button>
                                                        <?php 
                                                        } elseif($status == "rejected") {
                                                        ?>
                                                            <button type="button" class="btn btn-danger"><i class="fa fa-close"></i> Cancelled</button>
                                                        <?php 
                                                        } 
                                                        ?>
                                                    </td>
                                                    <td data-column="Date"><?php echo $row['date']; ?></td>
                                                    <td data-column="Action">
                                                        <a href="delete_orders.php?order_del=<?php echo $row['o_id'];?>" onclick="return confirm('Are you sure you want to cancel your order?');" class="btn btn-danger btn-flat btn-addon btn-xs m-b-10">
                                                            <i class="fa fa-trash-o" style="font-size:16px"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                        <?php 
                                            }
                                        } 
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="footer">
            <div class="row bottom-footer">
                <div class="container">
                    <div class="row">
                        <div class="col-xs-12 col-sm-3 payment-options color-gray">
                            <h5>Payment Options</h5>
                            <ul>
                                <li><a href="#"><img src="images/paypal.png" alt="Paypal"></a></li>
                                <li><a href="#"><img src="images/mastercard.png" alt="Mastercard"></a></li>
                                <li><a href="#"><img src="images/maestro.png" alt="Maestro"></a></li>
                                <li><a href="#"><img src="images/stripe.png" alt="Stripe"></a></li>
                                <li><a href="#"><img src="images/bitcoin.png" alt="Bitcoin"></a></li>
                            </ul>
                        </div>
                        <div class="col-xs-12 col-sm-4 address color-gray">
                            <h5>Address</h5>
                            <p>Alsoaps Outering Road</p>
                            <h5>Phone: +254 768343346</h5>
                        </div>
                        <div class="col-xs-12 col-sm-5 additional-info color-gray">
                            <h5>Additional informations</h5>
                            <p>Join thousands of other restaurants who benefit from having partnered with us.</p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
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