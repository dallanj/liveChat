<?php
require '../inc/db.php';
require '../classes/send_mail.php';
	
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['loggedin'] === 1) {


    $oldPass = mysqli_real_escape_string($conn,$_POST['password']);
    $newPass = mysqli_real_escape_string($conn,$_POST['newPassword']);
    $confirm = mysqli_real_escape_string($conn,$_POST['confirmPassword']);
    $id = $_SESSION['UID'];

    if(empty($oldPass) || empty($newPass) || empty($confirm)) {
        $_SESSION['error'] = 'Please fill in all fields';
    } else if(strlen($newPass) < 8) {
        $_SESSION['error'] = 'Your password needs to be a minimum of 8 characters';
    } else if(preg_match("/[A-Z]/", $newPass) === 0 || preg_match("/[a-z]/", $newPass) === 0) {
        $_SESSION['error'] = 'Your password must contain atleast one uppercase and lowercase letter';
    } else if(preg_match("/[0-9]/", $newPass) === 0) {
        $_SESSION['error'] = 'Your password must contain atleast one number';
    } else if ((!ctype_alnum($newPass)) || (!ctype_alnum($confirm))) {
        $_SESSION['error'] = 'Passwords may only contain letters and numbers';
    } else {
  
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = mysqli_stmt_init($conn);

        if(!mysqli_stmt_prepare($stmt, $sql)) {
            $_SESSION['error'] = 'There was an error, try again later';
        } else {
            mysqli_stmt_bind_param($stmt, "s", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if(!$row = mysqli_fetch_assoc($result)) {
                $_SESSION['error'] = 'There was an error, try again later';
            } else {

                if($newPass == $confirm) {
                    if(password_verify($oldPass, $row['password'])) {
                        if($oldPass === $newPass) {
                            $_SESSION['error'] = "You must use a different password then your current one";
                        } else {
                            $hash = password_hash($newPass, PASSWORD_DEFAULT);
                            $sql = "UPDATE users SET password = ? WHERE id = ?";
                            $stmt = mysqli_stmt_init($conn);

                            if(!mysqli_stmt_prepare($stmt, $sql)) {
                                $_SESSION['error'] = 'There was an error, try again later';
                            } else {
                                mysqli_stmt_bind_param($stmt, "ss", $hash, $id);
                                mysqli_stmt_execute($stmt);

                                $to = $_SESSION['EMAIL'];

                                $subject = "Password update confirmation";

                                $message = "<p>You updated your password</p>";
                                $message .= "<p>Here is your email verification link:</br>";

                                $result = sendMail($to,$subject,$message);
                                if($result == 'error') {
                                    $_SESSION['error'] = 'Error! Could not send confirmation email, however your password has been updated';
                                }

                                $_SESSION['success'] = 'Success! You have updated your password';
                            }
                        }
                    } else {
                        $_SESSION['error'] = 'Wrong password provided';
                    }
                } else {
                    $_SESSION['error'] = 'Your passwords do not match';
                }
            }
        }
    } 
    header("Location: ../dashboard.php?update=password");
} else {
    $_SESSION['error'] = 'Error! You do not have permission to access that request';
    header("Location: ../index.php");
    die();
}