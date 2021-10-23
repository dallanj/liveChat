<?php
require '../inc/db.php';
require '../classes/send_mail.php';
	
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['loggedin'] === 1) {

    $password = mysqli_real_escape_string($conn,$_POST['password']);
    $notify = $_POST['notify'];
    $security = $_POST['2fa'];
    $id = $_SESSION['UID'];

    if(empty($password)) {
        $_SESSION['error'] = 'Please enter your password'; 
    } else {

        if(isset($notify)) {
            $notify = 1;
        } else {
            $notify = 0;
        }
        if(isset($security)) {
            $security = 1;
        } else {
            $security = 0;
        }
  
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

                if(password_verify($password, $row['password'])) {
                    
                    $sql = "UPDATE users SET notify = ?, 2fa = ? WHERE id = ?";
                    $stmt = mysqli_stmt_init($conn);

                    if(!mysqli_stmt_prepare($stmt, $sql)) {
                        $_SESSION['error'] = 'There was an error, try again later';
                    } else {
                        mysqli_stmt_bind_param($stmt, "sss", $notify, $security, $id);
                        mysqli_stmt_execute($stmt);

                        if($_SESSION['security'] !== 1 && $security === 1) {
                            $to = $_SESSION['EMAIL'];

                            $subject = "2FA update confirmation";

                            $message = "<p>You added 2FA to your account</p>";

                            $result = sendMail($to,$subject,$message);
                            if($result == 'error') {
                                $_SESSION['error'] = 'Error! Could not send confirmation email, however you have enabled 2FA to your account';
                                header("Location: ../index.php");
                            }
                        }

                        $_SESSION['success'] = 'Success! You have updated your preferences';
                        header("Location: ../index.php");
                    }
                    
                } else {
                    $_SESSION['error'] = 'Wrong password provided';
                    header("Location: ../index.php");
                }
            }
        }
    }
} else {
    $_SESSION['error'] = 'Error! You do not have permission to access that request';
    header("Location: ../index.php");
    die();
}