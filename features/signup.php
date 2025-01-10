<?php 
session_start();

include("../conn/connection.php");
include("../conn/function.php");
$error_message = "";

if($_SERVER['REQUEST_METHOD'] == "POST")
{
    // something was posted
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $user_name = $_POST['user_name'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    $role = $_POST['role'];

    // Check for SQL delimiter characters
    $delimiter_pattern = '/[;\'"]/';

    if (preg_match($delimiter_pattern, $fname) || preg_match($delimiter_pattern, $lname) || preg_match($delimiter_pattern, $user_name) || preg_match($delimiter_pattern, $password) || preg_match($delimiter_pattern, $role)) {
        $error_message = "Inputs cannot contain SQL delimiter characters such as ;, ', or \"";
    } else {
        if (!empty($user_name) && !empty($password) && !is_numeric($user_name)) {
            $u = "SELECT user_name FROM user_db WHERE user_name='$user_name'";
            $uu = mysqli_query($con, $u);

            if (mysqli_num_rows($uu) <= 0) {
                if ($cpassword == $password) {

                    // Server-side validation for alphanumeric and underscore
                    if (preg_match('/^[a-zA-Z0-9_]+$/', $user_name) && preg_match('/^[a-zA-Z0-9_]{8,}$/', $password)) {
                        $h_password = password_hash($password, PASSWORD_DEFAULT);

                        // save to database
                        $user_id = random_num(20);
                        $query = "INSERT INTO user_db (user_id, fname, lname, user_name, password, role) VALUES ('$user_id', '$fname', '$lname', '$user_name', '$h_password', '$role')";

                        mysqli_query($con, $query);

                        header("Location: ./login.php");
                        die;
                    } else {
                        $error_message = "Username and password can only contain letters, numbers, and underscores. Password must be at least 8 characters long.";
                    }   
                } else {
                    $error_message = "Passwords don't match";
                }
            } else {
                $error_message = "Username exists";
            }
        } else {
            $error_message = "Please enter some valid information!";
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="sign.css">
<style>
    body{
        background-color: rgb(255, 255, 255);
    }
    input[type=text], input[type=password], input[type=date]{
  width: 100%;
  padding: 12px 20px;
  margin: 8px 0;
  display: inline-block;
  border: 2px solid black;
  border-radius: 10px;
  box-sizing: border-box;
}
    .user{
  width: 100%;
  padding: 12px 20px;
  margin: 8px 0;
  display: inline-block;
  border: 2px solid black;
  border-radius: 10px;
  box-sizing: border-box;
}
.back {
  width: 40px;
  height: 40px;
  background-image: url('./icons/arrow.png');
  background-size: cover;
  background-position: center;
  transition: background-image 0.3s;
}

.back:hover {
  background-image: url('./icons/arrow2.png');
  cursor: pointer;
}
</style>
<script>
    function goBack() {
            window.history.back();
        }
</script>
<title>Signup</title>
</head>
<body>
<div class="back" onclick="goBack()"></div>
<br/>
<div class="flex items-center">
        <img src="../img/header_logo.svg" alt="Cafe Logo" class="object-cover">
    </div>
<hr>
<h1>Add Admin/Staff</h1>
<p>Please fill in this form to create an account.</p>
<form method="post">
  <div class="container">
  <label for="fname"><b>First Name</b></label>
  <input type="text" placeholder="Enter First Name" name="fname" required>

  <label for="lname"><b>Last Name</b></label>
  <input type="text" placeholder="Enter Last Name" name="lname" required>

    <label for="user_name"><b>Username</b></label>
    <input type="text" placeholder="Enter Username" name="user_name" required>

    <label for="password"><b>Password</b></label>
    <input type="password" placeholder="Enter Password" name="password" required onkeyup="checkPasswordStrength()">

    <div class="strength" id="strength-bar">
      <span></span>
    </div>

    <label for="cpassword"><b>Confirm Password</b></label>
    <input type="password" placeholder="Enter Password" name="cpassword" required>

    <label for="role"><b>User Type</b></label>
    <select name="role" class="user"required>
            <option value="1">Admin</option>
            <option value="0">Staff</option>
        </select>

    <div id="error-message" class="error"><?php echo $error_message; ?></div>
        
    <button type="submit">ADD USER</button>
  </div>

  <div class="container" style="background-color:#f1f1f1">
  </div>
</form>

</body>
</html>
