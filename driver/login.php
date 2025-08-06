<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Driver Login</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
    <link rel='stylesheet prefetch' href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,500,700,900|RobotoDraft:400,100,300,500,700,900'>
    <link rel='stylesheet prefetch' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css'>
    <link rel="stylesheet" href="../css/login.css">

    <style type="text/css">
        #buttn {
            color: #fff;
            background-color: #5c4ac7;
        }
        .driver-login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .driver-login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .driver-login-header h2 {
            color: #5c4ac7;
            margin-bottom: 10px;
        }
        .driver-login-header p {
            color: #666;
            font-size: 14px;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/animsition.min.css" rel="stylesheet">
    <link href="../css/animate.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>

<body>
    <header id="header" class="header-scroll top-header headrom">
        <nav class="navbar navbar-dark">
            <div class="container">
                <button class="navbar-toggler hidden-lg-up" type="button" data-toggle="collapse" data-target="#mainNavbarCollapse">&#9776;</button>
                <a class="navbar-brand" href="../index.php"> <img class="img-rounded" src="../images/icn.png" alt=""> </a>
                <div class="collapse navbar-toggleable-md float-lg-right" id="mainNavbarCollapse">
                    <ul class="nav navbar-nav">
                        <li class="nav-item"> <a class="nav-link active" href="../index.php">Home <span class="sr-only">(current)</span></a> </li>
                        <li class="nav-item"> <a class="nav-link active" href="../restaurants.php">Restaurants <span class="sr-only"></span></a> </li>
                        <li class="nav-item"> <a class="nav-link active" href="login.php">Driver Login</a> </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div style="background-image: url('../images/img/pimg.jpg'); min-height: 100vh; background-size: cover; background-position: center; padding: 20px;">
        <div class="driver-login-container">
            <div class="driver-login-header">
                <h2><i class="fa fa-motorcycle"></i> Driver Login</h2>
                <p>Access your delivery dashboard</p>
            </div>

            <?php
            include("../connection/connect.php");
            ini_set('display_errors', 0);
            ini_set('log_errors', 1);
            ini_set('error_log', __DIR__.'/error.log');
            error_reporting(E_ALL);

            session_start();
            session_regenerate_id(true);

            $error_message = '';
            $success_message = '';

            if (isset($_POST['submit'])) {
                $phone = mysqli_real_escape_string($db, trim($_POST['phone']));
                $password = trim($_POST['password']);

                if (!empty($phone) && !empty($password)) {
                    // Query driver by phone number
                    $query = "SELECT * FROM drivers WHERE phone = ?";
                    $stmt = $db->prepare($query);
                    $stmt->bind_param("s", $phone);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $driver = $result->fetch_assoc();
                        
                        // Check password against database
                        if ($password === $driver['password']) {
                            $_SESSION['driver_id'] = $driver['id'];
                            $_SESSION['driver_name'] = $driver['name'];
                            $success_message = "Login successful! Redirecting...";
                            
                            // Update driver status to available
                            $update_status = "UPDATE drivers SET status = 'available' WHERE id = ?";
                            $status_stmt = $db->prepare($update_status);
                            $status_stmt->bind_param("i", $driver['id']);
                            $status_stmt->execute();
                            
                            header("refresh:1;url=dashboard.php");
                        } else {
                            $error_message = "Invalid password. Please try again.";
                        }
                    } else {
                        $error_message = "Driver not found with this phone number.";
                    }
                } else {
                    $error_message = "Please enter both phone number and password.";
                }
            }
            ?>

            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter your phone number" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" name="submit" class="btn btn-primary btn-block" id="buttn">
                    <i class="fa fa-sign-in"></i> Login
                </button>
            </form>

            <div class="text-center mt-3">
                <small class="text-muted">
                    <strong>Demo Credentials:</strong><br>
                    Phone: +254700123456<br>
                    Password: driver123
                </small>
            </div>

            <div class="text-center mt-3">
                <p>Don't have an account? <a href="register.php" class="text-primary">Register here</a></p>
            </div>

            <div class="text-center mt-3">
                <a href="../index.php" class="text-muted">
                    <i class="fa fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </div>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/tether.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/animsition.min.js"></script>
    <script src="../js/bootstrap-slider.min.js"></script>
    <script src="../js/jquery.isotope.min.js"></script>
    <script src="../js/headroom.js"></script>
    <script src="../js/foodpicky.min.js"></script>
</body>

</html> 