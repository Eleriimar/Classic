<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Driver Registration</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
    <link rel='stylesheet prefetch' href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,500,700,900|RobotoDraft:400,100,300,500,700,900'>
    <link rel='stylesheet prefetch' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css'>
    <link rel="stylesheet" href="../css/login.css">

    <style type="text/css">
        #buttn {
            color: #fff;
            background-color: #5c4ac7;
        }
        .driver-register-container {
            max-width: 500px;
            margin: 30px auto;
            padding: 25px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .driver-register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .driver-register-header h2 {
            color: #5c4ac7;
            margin-bottom: 10px;
        }
        .driver-register-header p {
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
        .form-row {
            display: flex;
            gap: 15px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #5c4ac7;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
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
                        <li class="nav-item"> <a class="nav-link active" href="register.php">Driver Register</a> </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div style="background-image: url('../images/img/pimg.jpg'); min-height: 100vh; background-size: cover; background-position: center; padding: 20px;">
        <div class="driver-register-container">
            <div class="driver-register-header">
                <h2><i class="fa fa-motorcycle"></i> Driver Registration</h2>
                <p>Join our delivery team and start earning</p>
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
                $name = mysqli_real_escape_string($db, trim($_POST['name']));
                $phone = mysqli_real_escape_string($db, trim($_POST['phone']));
                $email = mysqli_real_escape_string($db, trim($_POST['email']));
                $vehicle_number = mysqli_real_escape_string($db, trim($_POST['vehicle_number']));
                $vehicle_type = mysqli_real_escape_string($db, trim($_POST['vehicle_type']));
                $password = trim($_POST['password']);
                $confirm_password = trim($_POST['confirm_password']);

                // Validation
                $errors = array();

                if (empty($name)) {
                    $errors[] = "Name is required";
                }

                if (empty($phone)) {
                    $errors[] = "Phone number is required";
                } elseif (!preg_match("/^\+?[0-9]{10,15}$/", $phone)) {
                    $errors[] = "Please enter a valid phone number";
                }

                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Please enter a valid email address";
                }

                if (empty($vehicle_number)) {
                    $errors[] = "Vehicle number is required";
                }

                if (empty($vehicle_type)) {
                    $errors[] = "Vehicle type is required";
                }

                if (empty($password)) {
                    $errors[] = "Password is required";
                } elseif (strlen($password) < 6) {
                    $errors[] = "Password must be at least 6 characters long";
                }

                if ($password !== $confirm_password) {
                    $errors[] = "Passwords do not match";
                }

                // Check if phone number already exists
                $check_phone = "SELECT id FROM drivers WHERE phone = ?";
                $check_stmt = $db->prepare($check_phone);
                $check_stmt->bind_param("s", $phone);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $errors[] = "A driver with this phone number already exists";
                }

                if (empty($errors)) {
                    // Insert new driver
                    $insert_query = "INSERT INTO drivers (name, phone, email, vehicle_number, vehicle_type, password, status) VALUES (?, ?, ?, ?, ?, ?, 'available')";
                    $insert_stmt = $db->prepare($insert_query);
                    $insert_stmt->bind_param("ssssss", $name, $phone, $email, $vehicle_number, $vehicle_type, $password);
                    
                    if ($insert_stmt->execute()) {
                        $success_message = "Registration successful! You can now login with your phone number and password.";
                    } else {
                        $error_message = "Registration failed. Please try again.";
                    }
                } else {
                    $error_message = implode("<br>", $errors);
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
                    <label for="name">Full Name *</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="text" class="form-control" id="phone" name="phone" placeholder="e.g., +254700123456" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="vehicle_number">Vehicle Number *</label>
                        <input type="text" class="form-control" id="vehicle_number" name="vehicle_number" placeholder="e.g., KCA 123A" required>
                    </div>

                    <div class="form-group">
                        <label for="vehicle_type">Vehicle Type *</label>
                        <select class="form-control" id="vehicle_type" name="vehicle_type" required>
                            <option value="">Select vehicle type</option>
                            <option value="Motorcycle">Motorcycle</option>
                            <option value="Bicycle">Bicycle</option>
                            <option value="Car">Car</option>
                            <option value="Van">Van</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password (min 6 characters)" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-primary btn-block" id="buttn">
                    <i class="fa fa-user-plus"></i> Register as Driver
                </button>
            </form>

            <div class="login-link">
                <p>Already have an account? <a href="login.php">Login here</a></p>
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