<?php 

session_start();

include("../folderdb/connection.php");
include("../folderdb/function.php");


	if($_SERVER['REQUEST_METHOD'] == "POST")
	{
		//something was posted
		$user_name = $_POST['user_name'];
		$password = $_POST['password'];

		if(!empty($user_name) && !empty($password) && !is_numeric($user_name))
		{

			//read from database
			$query = "select * from users where user_name = '$user_name' limit 1";
			$result = mysqli_query($con, $query);

			if($result)
			{
				if($result && mysqli_num_rows($result) > 0)
				{

					$user_data = mysqli_fetch_assoc($result);
					
					if(password_verify($password, $user_data['password']))
					{

						$_SESSION['user_id'] = $user_data['user_id'];
						header("Location: index.php");
						die;
					}
				}
			}
			
			echo "wrong username or password!";
		}else
		{
			echo "wrong username or password!";
		}
	}

?>


<!DOCTYPE html>
<html>
<head>
	<title>Login</title>
	<link rel="stylesheet" href="sign.css">
	<style>
    body{
        background-color: rgb(242, 219, 255);
    }
    input[type=text], input[type=password] {
  width: 100%;
  padding: 12px 20px;
  margin: 8px 0;
  display: inline-block;
  border: 2px solid purple;
  border-radius: 10px;
  box-sizing: border-box;
}
	</style>
</head>
<body>
<div class = "logo">
        <img width= "100"src = "logo.png">
    </div>
	<hr>
<h1>Sign In</h1>
<form method="post">
  <div class="container">
    <label for="user_name"><b>Username</b></label>
    <input type="text" placeholder="Enter Username" name="user_name" required>

    <label for="password"><b>Password</b></label>
    <input type="password" placeholder="Enter Password" name="password" required>
        
    <button type="submit">Sign in</button>
  </div>

  <div class="container" style="background-color:#f1f1f1">
  </div>
</form>

</body>
</html>