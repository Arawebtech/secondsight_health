<?php
session_start();
include("admin/inc/config.php");
$user_id = "";
if (isset($_SESSION['user_id']))
    $user_id = $_SESSION['user_id'];
else if (isset($_SESSION['temp_user_id']))
    $user_id = $_SESSION['temp_user_id'];

// Handle form submission before any HTML output
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["register"])) {
    $UserName   = mysqli_real_escape_string($con, $_POST["UserName"] ?? '');
    $email      = mysqli_real_escape_string($con, $_POST["email"] ?? '');
    $password   = $_POST["password"] ?? '';
    $Mobile     = mysqli_real_escape_string($con, $_POST["Mobile"] ?? '');
    
    $b_name = $b_mobile = $b_address = $b_town = $b_city = $b_state = $b_pincode = $b_landmark = "";
    $s_name = $s_mobile = $s_address = $s_town = $s_city = $s_state = $s_pincode = $s_landmark = "";

    $query = "INSERT INTO tbl_register (
        UserName, email, password, Mobile,
        b_name, b_mobile, b_address, b_town, b_city, b_state, b_pincode, b_landmark,
        s_name, s_mobile, s_address, s_town, s_city, s_state, s_pincode, s_landmark
    ) VALUES (
        '$UserName', '$email', '$password', '$Mobile',
        '$b_name', '$b_mobile', '$b_address', '$b_town', '$b_city', '$b_state', '$b_pincode', '$b_landmark',
        '$s_name', '$s_mobile', '$s_address', '$s_town', '$s_city', '$s_state', '$s_pincode', '$s_landmark'
    )";

    if (mysqli_query($con, $query)) {
        header("Location: login.php");
        exit;
    } else {
        $error = "Failed to save data. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Register</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="libs/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .register-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .box-form-login {
            border: 1px solid #eee;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        label.required:after {
            content: " *";
            color: red;
        }
    </style>
</head>

<body>
    <?php include("include/header.php"); ?>

    <div class="container my-5">
        <div class="shadow bg-white p-4 rounded">
            <h4 class="text-center py-2 text-white" style="background: linear-gradient(to right, #ffb200, #fd9800);">Registration</h4>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="row justify-content-center">
                    <div class="col-md-5">
                        <div class="box-form-login">
                            <div class="register-title text-center">Sign Up</div>
                            <div class="mb-3">
                                <label class="required">Full Name</label>
                                <input type="text" name="UserName" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="required">Mobile Number</label>
                                <input type="text" name="Mobile" class="form-control" placeholder="Enter 10-digit mobile number" required>
                            </div>
                            <div class="mb-3">
                                <label class="required">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="learningpoint0786@gmail.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="required">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="•••••" required>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" name="register" class="btn btn-warning px-5 py-2 text-white fw-bold" style="background: linear-gradient(to right, #ffb200, #fd9800); border: none; width: 100%;">Register</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
    <script src="libs/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>