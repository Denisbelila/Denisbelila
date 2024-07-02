<?php
include 'config.php';


$message = array(); // Initialize an array to store messages

if(isset($_POST['submit'])){
   $filter_name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $name = mysqli_real_escape_string($conn, $filter_name);
   $filter_email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
   $email = mysqli_real_escape_string($conn, $filter_email);
   $filter_pass = filter_var($_POST['pass'], FILTER_SANITIZE_STRING);
   $pass = mysqli_real_escape_string($conn, md5($filter_pass));
   $filter_cpass = filter_var($_POST['cpass'], FILTER_SANITIZE_STRING);
   $cpass = mysqli_real_escape_string($conn, md5($filter_cpass));

   $select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE email = '$email'");

   if(mysqli_num_rows($select_users) > 0){
      $message[] = 'User already exists!';
   } else {
      if($pass != $cpass){
         $message[] = 'Confirm password does not match!';
      } else {
         mysqli_query($conn, "INSERT INTO `users` (name, email, password) VALUES ('$name', '$email', '$pass')");
         $message[] = 'Registered successfully!';
         header('location: login.php'); // Redirect to verify.php
         exit; // Ensure that script execution stops after redirection
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
     
         * {
            font-family: tahoma;
            font-size: 13px;
        }

        body {
            background: linear-gradient(to right, #FFB6C1, #FFB6C1);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #fff;
        }

        form {
            background: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }

        h1 {
            margin-bottom: 20px;
        }

        .textbox {
            padding: 10px;
            width: 100%;
            margin-bottom: 10px;
            border: none;
            border-radius: 5px;
            box-sizing: border-box;
        }

        input[type="submit"], input[type="button"] {
            padding: 10px;
            width: 100%;
            border: none;
            border-radius: 5px;
            background:#FFB6C1;
            color: #fff;
            font-size: 15px;
            cursor: pointer;
        }

        input[type="submit"]:hover, input[type="button"]:hover {
            background: #2575fc;
        }

        .error {
            font-size: 12px;
            color: red;
            margin-bottom: 10px;
        }

        .link {
            text-align: center;
            margin-top: 20px;
        }

        .link a {
            color: #fff;
            text-decoration: none;
        }

        .link a:hover {
            text-decoration: underline;
        }
   </style>
</head>
<body>

<?php
if(isset($message)){
   foreach($message as $msg){
      echo '
      <div class="message">
         <span>'.$msg.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<section class="form-container">
   <form action="" method="post">
      <h3>Register Now</h3>
      <input type="text" name="name" class="box" placeholder="Enter your username" required>
      <input type="email" name="email" class="box" placeholder="Enter your email" required>
      <input type="password" name="pass" class="box" placeholder="Enter your password" required>
      <input type="password" name="cpass" class="box" placeholder="Confirm your password" required>
      <input type="submit" class="btn" name="submit" value="Register Now">
      <p>Already have an account? <a href="login.php">Login Now</a></p>
   </form>
</section>

</body>
</html>
