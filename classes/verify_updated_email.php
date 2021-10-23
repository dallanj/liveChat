<?php
require '../inc/db.php';
require '../classes/send_mail.php';

// Start of Registration with Prepared Statements

// if by clicking link in email
$selector = $_GET['selector'];
$validator = $_GET['validator'];

if(!empty($selector) && !empty($validator) && $_SESSION['loggedin'] === 1) {
    if(ctype_xdigit($selector) !== false && ctype_xdigit($validator) !== false) {

        $sql = "SELECT * FROM email_tokens WHERE selector=?";
        $stmt = mysqli_stmt_init($conn);

        if(!mysqli_stmt_prepare($stmt, $sql)) {
            $_SESSION['error'] = 'There was an error, try again later';
            header("Location: ../dashboard.php?update=email");
        } else {
            mysqli_stmt_bind_param($stmt, "s", $selector);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            if(!$row = mysqli_fetch_assoc($result)) {
                $_SESSION['error'] = 'Error! You need to resend a new email token request';
                header("Location: ../dashboard.php?update=email");
            } else {
                if(isset($row['expires']) && time() > $row['expires']+3600) {

                    // Start of Delete Token
                    $sql = "DELETE FROM email_tokens WHERE selector=?";
                    $stmt = mysqli_stmt_init($conn);
                    mysqli_stmt_prepare($stmt, $sql);
                    mysqli_stmt_bind_param($stmt, "s", $selector);
                    mysqli_stmt_execute($stmt);     
                    // End of Delete Token

                    $_SESSION['error'] = 'Error! Your email token has expired, please request a new one';
                    header("Location: ../dashboard.php?update=email"); 
                } else {

                    $tokenBin = hex2bin($validator);
                    $tokenCheck = password_verify($tokenBin, $row['token']);

                    if($tokenCheck === false) {
                        $_SESSION['error'] = 'Error! You need to resend a new email token request';
                        header("Location: ../dashboard.php?update=email");
                    } else if($tokenCheck === true) {
                        $tokenEmail = $row['user_id'];

                        $sql = "SELECT * FROM users WHERE email=?";
                        $stmt = mysqli_stmt_init($conn);

                        if(!mysqli_stmt_prepare($stmt, $sql)) {
                            $_SESSION['error'] = 'There was an error, try again later';
                            header("Location: ../dashboard.php?update=email");
                        } else {
                            mysqli_stmt_bind_param($stmt, "s", $_SESSION['EMAIL']);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            if(!$row = mysqli_fetch_assoc($result)) {
                                $_SESSION['error'] = 'There was an error, try again later';
                                header("Location: ../dashboard.php?update=email");
                            } else {
                                $sql = "UPDATE users SET email = ? WHERE id = ?";
                                $stmt = mysqli_stmt_init($conn);

                                if(!mysqli_stmt_prepare($stmt, $sql)) {
                                    $_SESSION['error'] = 'There was an error, try again later';
                                    header("Location: ../dashboard.php?update=email");
                                } else {
                                    mysqli_stmt_bind_param($stmt, "ss", $tokenEmail, $_SESSION['UID']);
                                    mysqli_stmt_execute($stmt);

                                    // Start of Delete Token
                                        $sql = "DELETE FROM email_tokens WHERE user_id=?";
                                        $stmt = mysqli_stmt_init($conn);
                                        mysqli_stmt_prepare($stmt, $sql);
                                        mysqli_stmt_bind_param($stmt, "s", $tokenEmail);
                                        mysqli_stmt_execute($stmt);     
                                    // End of Delete Token

                                    unset($_SESSION['token']);
                                    unset($_SESSION['tokenEmail']);

                                    $to = $tokenEmail;

                                    $subject = "Email update confirmation";

                                    $message = "<p>You changed your email address</p>";
                                    $message .= "<p>Here is your email verification link:</br>";

                                    $result = sendMail($to,$subject,$message);
                                    if($result == 'error') {
                                        $_SESSION['error'] = 'Error! Could not send confirmation email, however your email has been updated';
                                    }

                                    $_SESSION['success'] = 'Success! Your email has been verified and updated';
                                    header("Location: ../dashboard.php?update=email");
                                }
                            }
                        }
                    }
                }
            }
        }

    }
} else {
    $_SESSION['error'] = 'Error! You need to be logged in to perform this request';
    header("Location: ../index.php");
}


// if by entering code
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recaptcha_response']) && $_SESSION['loggedin'] === 1) {
        // Build POST request:
        require '../classes/recaptcha.php';

        // Take action based on the score returned:
        if ($recaptcha->score >= 0.5) {
            // Verified - send email
            $mycode = $_POST['code'];

            if(empty($mycode)) {
                $_SESSION['error'] = 'Please fill in your verification code';
                header("Location: ../dashboard.php?verify=email");
            } else {

                $sql = "SELECT * FROM email_tokens WHERE user_id = ?";
                $stmt = mysqli_stmt_init($conn);
                if(!mysqli_stmt_prepare($stmt, $sql)) {
                    $_SESSION['error'] = 'The server is having trouble logging in, try again later';
                } else {
                    mysqli_stmt_bind_param($stmt, "s", $_SESSION['tokenEmail']);
                    mysqli_stmt_execute($stmt);

                    $result = mysqli_stmt_get_result($stmt);
                    $count = mysqli_num_rows($result);
                    if($count == 0) {
                        $_SESSION['error'] = 'Error! Email address not found';
                        header("Location: ../index.php");
                    }
                    if($count == 1) { 
                        while($row = mysqli_fetch_assoc($result)) {
                            if($mycode != $row['code']){
                                $_SESSION['error'] = 'Error! You submitted a wrong verification code';
                                header("Location: ../dashboard.php?verify=email");
                            } else {   
                                if(isset($row['expires']) && time() > $row['expires']+3600) {

                                    // Start of Delete Token
                                    $sql = "DELETE FROM email_tokens WHERE user_id=?";
                                    $stmt = mysqli_stmt_init($conn);
                                    mysqli_stmt_prepare($stmt, $sql);
                                    mysqli_stmt_bind_param($stmt, "s", $_SESSION['tokenEmail']);
                                    mysqli_stmt_execute($stmt);     
                                    // End of Delete Token

                                    $_SESSION['error'] = 'Error! Your email token code has expired, please request a new one';
                                    header("Location: ../dashboard.php?update=email"); 
                                } else {
                                    if(password_verify($_SESSION['token'], $row['token'])) {

                                        $sql = "UPDATE users SET email = ? WHERE id = ?";
                                        $stmt = mysqli_stmt_init($conn);

                                        if(!mysqli_stmt_prepare($stmt, $sql)) {
                                            $_SESSION['error'] = 'There was an error, try again later';
                                        } else {
                                            mysqli_stmt_bind_param($stmt, "ss", $_SESSION['tokenEmail'], $_SESSION['UID']);
                                            mysqli_stmt_execute($stmt);

                                            // Start of Delete Token
                                                $sql = "DELETE FROM email_tokens WHERE user_id=?";
                                                $stmt = mysqli_stmt_init($conn);
                                                mysqli_stmt_prepare($stmt, $sql);
                                                mysqli_stmt_bind_param($stmt, "s", $_SESSION['tokenEmail']);
                                                mysqli_stmt_execute($stmt);     
                                            // End of Delete Token
                                            
                                            $tokenEmail = $_SESSION['tokenEmail'];
                                            unset($_SESSION['token']);
                                            unset($_SESSION['tokenEmail']);

                                            $to = $tokenEmail;

                                            $subject = "Email update confirmation";

                                            $message = "<p>You changed your email address</p>";
                                            $message .= "<p>Here is your email verification link:</br>";

                                            $result = sendMail($to,$subject,$message);
                                            if($result == 'error') {
                                                $_SESSION['error'] = 'Error! Could not send confirmation email, however your email has been updated';
                                            }

                                            $_SESSION['success'] = 'Success! Your email has been verified and updated';
                                            
                                            header("Location: ../dashboard.php?update=email");
                                        }
                                    }
                                }
                                
                            }
                        }
                    }
                    

                }
            }
        } else {
            // Not verified - show form error
            $_SESSION['error'] = 'Error! Captcha has failed, please try again';
            header("Location: ../dashboard.php?verify=email");
        }       
    }

// End of Registration with Prepared Statements





