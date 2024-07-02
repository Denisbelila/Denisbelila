<?php 
session_start();
$error = array();

require "mail.php";

if (!$con = mysqli_connect("localhost", "root", "", "denden")) {
    die("Could not connect");
}

$mode = "enter_email";
if (isset($_GET['mode'])) {
    $mode = $_GET['mode'];
}

if (count($_POST) > 0) {
    switch ($mode) {
        case 'enter_email':
            $email = $_POST['email'];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error[] = "Please enter a valid email";
            } elseif (!valid_email($email)) {
                $error[] = "That email was not found";
            } else {
                $_SESSION['forgot']['email'] = $email;
                send_email($email);
                header("Location: forgot.php?mode=enter_code");
                die;
            }
            break;

        case 'enter_code':
            $code = $_POST['code'];
            $result = is_code_correct($code);
            if ($result == "the code is correct") {
                $_SESSION['forgot']['code'] = $code;
                header("Location: forgot.php?mode=enter_password");
                die;
            } else {
                $error[] = $result;
            }
            break;

        case 'enter_password':
            $password = $_POST['password'];
            $password2 = $_POST['password2'];
            if ($password !== $password2) {
                $error[] = "Passwords do not match";
            } elseif (!isset($_SESSION['forgot']['email']) || !isset($_SESSION['forgot']['code'])) {
                header("Location: forgot.php");
                die;
            } else {
                save_password($password);
                if (isset($_SESSION['forgot'])) {
                    unset($_SESSION['forgot']);
                }
                header("Location: login.php");
                die;
            }
            break;
        
        default:
            break;
    }
}

function send_email($email) {
    global $con;
    $expire = time() + (60 * 10);
    $code = rand(10000, 99999);
    $email = addslashes($email);
    $query = "INSERT INTO codes (email, code, expire) VALUES ('$email', '$code', '$expire')";
    mysqli_query($con, $query);
    send_mail($email, 'Password reset', "Your code is " . $code);
}

function save_password($password) {
    global $con;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Use password_hash function here
    $email = addslashes($_SESSION['forgot']['email']);
    $query = "UPDATE users SET password = '$hashed_password' WHERE email = '$email' LIMIT 1";
    mysqli_query($con, $query);
}

function valid_email($email) {
    global $con;
    $email = addslashes($email);
    $query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";     
    $result = mysqli_query($con, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        return true;
    }
    return false;
}

function is_code_correct($code) {
    global $con;
    $code = addslashes($code);
    $expire = time();
    $email = addslashes($_SESSION['forgot']['email']);
    $query = "SELECT * FROM codes WHERE code = '$code' AND email = '$email' ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($con, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($row['expire'] > $expire) {
            return "the code is correct";
        } else {
            return "the code is expired";
        }
    } else {
        return "the code is incorrect";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Forgot</title>
</head>
<body>
<style type="text/css">
    ** {
            font-family: tahoma;
            font-size: 13px;
        }

        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
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
            background: #6a11cb;
            color: #fff;
            font-size: 15px;
            cursor: pointer;
        }

        input[type="submit"]:hover, input[type="button"]:hover {
            background: z;
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
            color: white;
            text-decoration: none;
        }

        .link a:hover {
            text-decoration: underline;
        }
</style>

<?php 
switch ($mode) {
    case 'enter_email':
?>
<form method="post" action="forgot.php?mode=enter_email"> 
    <h1>Forgot Password</h1>
    <h3>Enter your email below</h3>
    <span style="font-size: 12px;color:red;">
    <?php 
        foreach ($error as $err) {
            echo $err . "<br>";
        }
    ?>
    </span>
    <input class="textbox" type="email" name="email" placeholder="Email"><br>
    <br style="clear: both">
    <input type="submit" value="Next">
    <br><br>
    <div><a href="login.php">Login</a></div>
</form>
<?php
    break;

    case 'enter_code':
?>
<form method="post" action="forgot.php?mode=enter_code"> 
    <h1>Forgot Password</h1>
    <h3>Enter the code sent to your email</h3>
    <span style="font-size: 12px;color:red;">
    <?php 
        foreach ($error as $err) {
            echo $err . "<br>";
        }
    ?>
    </span>
    <input class="textbox" type="text" name="code" placeholder="12345"><br>
    <br style="clear: both;">
    <input type="submit">;
	<a href="forgot.php">
        <input type="button" value="Start Over">
    </a>
    <br><br>
    <div><a href="login.php">Login</a></div>
</form>
<?php
    break;

    case 'enter_password':
?>
<form method="post" action="forgot.php?mode=enter_password"> 
    <h1>Forgot Password</h1>
    <h3>Enter your new password</h3>
    <span style="font-size: 12px;color:red;">
    <?php 
        foreach ($error as $err) {
            echo $err . "<br>";
        }
    ?>
    </span>
    <input class="textbox" type="password" name="password" placeholder="Password"><br>
    <input class="textbox" type="password" name="password2" placeholder="Retype Password"><br>
    <br style="clear: both;">
    <input type="submit" value="Reset Password" style="float: right;">
    <a href="forgot.php">
        <input type="button" value="Start Over">
    </a>
    <br><br>
    <div><a href="login.php">Login</a></div>
</form>
<?php
    break;
    
    default:
    break;
}
?>

</body>
</html>
