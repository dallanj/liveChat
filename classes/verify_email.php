<?php
require '../inc/db.php';

// Start of Registration with Prepared Statements

// if by clicking link in email
$selector = $_GET['selector'];
$validator = $_GET['validator'];

if(!empty($selector) && !empty($validator)) {
    if(ctype_xdigit($selector) !== false && ctype_xdigit($validator) !== false) {
        
        $currentDate = date("U");

        $sql = "SELECT * FROM email_tokens WHERE selector=?";
        $stmt = mysqli_stmt_init($conn);

        if(!mysqli_stmt_prepare($stmt, $sql)) {
            $_SESSION['error'] = 'There was an error, try again later';
            header("Location: ../forgot.php");
        } else {
            mysqli_stmt_bind_param($stmt, "s", $selector);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            if(!$row = mysqli_fetch_assoc($result)) {
                $_SESSION['error'] = 'Error! You need to resend a new password reset request';
                header("Location: ../forgot.php");
            } else {
                if(isset($row['expires']) && time() > $row['expires']+3600) {
                    $_SESSION['error'] = 'Error! Your 2FA code has expired, please log in again to request a new one';
                    header("Location: ../index.php"); 
                } else {

                    $tokenBin = hex2bin($validator);
                    $tokenCheck = password_verify($tokenBin, $row['token']);

                    if($tokenCheck === false) {
                        $_SESSION['error'] = 'Error! You need to resend a new password reset request';
                        header("Location: ../forgot.php");
                    } else if($tokenCheck === true) {
                        $verify = 1;
                        $tokenEmail = $row['user_id'];

                        $sql = "SELECT * FROM users WHERE email=?";
                        $stmt = mysqli_stmt_init($conn);

                        if(!mysqli_stmt_prepare($stmt, $sql)) {
                            $_SESSION['error'] = 'There was an error, try again later';
                            header("Location: ../forgot.php");
                        } else {
                            mysqli_stmt_bind_param($stmt, "s", $tokenEmail);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            if(!$row = mysqli_fetch_assoc($result)) {
                                $_SESSION['error'] = 'There was an error, try again later';
                                header("Location: ../forgot.php");
                            } else {
                                $sql = "UPDATE users SET verify = ? WHERE email = ?";
                                $stmt = mysqli_stmt_init($conn);

                                if(!mysqli_stmt_prepare($stmt, $sql)) {
                                    $_SESSION['error'] = 'There was an error, try again later';
                                } else {
                                    mysqli_stmt_bind_param($stmt, "ss", $verify, $tokenEmail);
                                    mysqli_stmt_execute($stmt);

                                    // Start of Delete Token
                                        $sql = "DELETE FROM email_tokens WHERE user_id=?";
                                        $stmt = mysqli_stmt_init($conn);
                                        mysqli_stmt_prepare($stmt, $sql);
                                        mysqli_stmt_bind_param($stmt, "s", $tokenEmail);
                                        mysqli_stmt_execute($stmt);     
                                    // End of Delete Token

                                    unset($_SESSION['token']);

                                    if($_SESSION['loggedin'] == 0) {
                                        $_SESSION['success'] = 'Success! Your email has been verified, you may now log in';
                                        header("Location: ../index.php"); 
                                    } elseif($_SESSION['loggedin'] == 2) {

                                        // Remember me script
                                        require '../classes/remember_me.php';

                                        $_SESSION['loggedin'] = 1;
                                        $_SESSION['success'] = 'Success! You are now logged in';
                                        if($_SESSION['first_login'] == 1) {
                                            header("Location: ../welcome.php"); 
                                        } else {
                                            header("Location: ../dashboard.php");
                                        }
                                    } else {
                                        $_SESSION['success'] = 'Success! Your email has been verified and you are now logged in';
                                        if($_SESSION['first_login'] == 1) {
                                            header("Location: ../welcome.php"); 
                                        } else {
                                            header("Location: ../dashboard.php");
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

    }
}


// if by entering code
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recaptcha_response'])) {
        // Build POST request:
        require '../classes/recaptcha.php';

        // Take action based on the score returned:
        if ($recaptcha->score >= 0.5) {
            // Verified - send email
            $mycode = $_POST['code'];
            $_2fa = $_POST['2fa'];

            if(empty($mycode)) {
                $_SESSION['error'] = 'Please fill in your verification code';
                header("Location: ../verify.php");
            } else {

                $sql = "SELECT * FROM email_tokens WHERE user_id = ?";
                $stmt = mysqli_stmt_init($conn);
                if(!mysqli_stmt_prepare($stmt, $sql)) {
                    $_SESSION['error'] = 'The server is having trouble logging in, try again later';
                } else {
                    mysqli_stmt_bind_param($stmt, "s", $_SESSION['EMAIL']);
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
                                $_SESSION['error'] = 'Error! You submitted a wrong verification code '.$row['code'];
                                header("Location: ../index.php");
                            } else {   
                                if(isset($row['expires']) && time() > $row['expires']+3600) {
                                    $_SESSION['error'] = 'Error! Your 2FA code has expired, please log in again to request a new one';
                                    header("Location: ../index.php"); 
                                } else {
                                    if(password_verify($_SESSION['token'], $row['token'])) {
                                        $verify = 1;
                                        if(isset($_2fa)) {
                                            $security = 1;
                                        } elseif($_SESSION['security'] == 1) {
                                            $security = 1;
                                        } else {
                                            $security = 0;
                                        }

                                        $sql = "UPDATE users SET verify = ?, 2fa = ? WHERE email = ?";
                                        $stmt = mysqli_stmt_init($conn);

                                        if(!mysqli_stmt_prepare($stmt, $sql)) {
                                            $_SESSION['error'] = 'There was an error, try again later';
                                        } else {
                                            mysqli_stmt_bind_param($stmt, "sss", $verify, $security, $_SESSION['EMAIL']);
                                            mysqli_stmt_execute($stmt);

                                            // Start of Delete Token
                                                $sql = "DELETE FROM email_tokens WHERE user_id=?";
                                                $stmt = mysqli_stmt_init($conn);
                                                mysqli_stmt_prepare($stmt, $sql);
                                                mysqli_stmt_bind_param($stmt, "s", $_SESSION['EMAIL']);
                                                mysqli_stmt_execute($stmt);     
                                            // End of Delete Token

                                            // Remember me script
                                            require '../classes/remember_me.php';
                                            
                                            unset($_SESSION['token']);

                                            if($_SESSION['loggedin'] == 0) {
                                                $_SESSION['success'] = 'Success! Your email has been verified, you may now log in';
                                                header("Location: ../index.php"); 
                                            } elseif($_SESSION['loggedin'] == 2) {
                                                $_SESSION['loggedin'] = 1;
                                                $_SESSION['success'] = 'Success! You are now logged in';
                                                if($_SESSION['first_login'] == 1) {
                                                    header("Location: ../welcome.php"); 
                                                } else {
                                                    header("Location: ../dashboard.php");
                                                }
                                            } else {
                                                $_SESSION['success'] = 'Success! Your email has been verified and you are now logged in';
                                                if($_SESSION['first_login'] == 1) {
                                                    header("Location: ../welcome.php"); 
                                                } else {
                                                    header("Location: ../dashboard.php");
                                                }
                                            }
                                            
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
            header("Location: ../verify.php");
        }       
    }

// End of Registration with Prepared Statements





