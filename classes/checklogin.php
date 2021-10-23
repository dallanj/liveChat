<?php
require '../inc/db.php';
require '../classes/send_mail.php';

// Start of Check Login Script
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recaptcha_response'])) {

        // Build POST request:
        require '../classes/recaptcha.php';

        // Take action based on the score returned:
        if ($recaptcha->score >= 0.5) {
            // Verified
            $myusername = mysqli_real_escape_string($conn,$_POST['username']);
            $mypassword = mysqli_real_escape_string($conn,$_POST['password']);
            $remember = $_POST['remember'];
            
            if(empty($myusername) || empty($mypassword)) {
                $_SESSION['error'] = 'Please fill in all fields';
                header("Location: ../index.php");
            }       

            $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
            $stmt = mysqli_stmt_init($conn);
            if(!mysqli_stmt_prepare($stmt, $sql)) {
                $_SESSION['error'] = 'The server is having trouble logging in, try again later';
                header("Location: ../index.php");
            } else {
                mysqli_stmt_bind_param($stmt, "ss", $myusername, $myusername);
                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);
                $count = mysqli_num_rows($result);

                if($count == 1) { 

                    while($row = mysqli_fetch_assoc($result)) {
                        if(password_verify($mypassword, $row['password'])) {
                            $_SESSION['UID'] = $row['id'];
                            $_SESSION['USERNAME'] = $row['username'];
                            $_SESSION['EMAIL'] = $row['email'];
                            $_SESSION['TYPE'] = $row['type'];
                            $_SESSION['first_login'] = $row['first_login'];
                            $_SESSION['security'] = $row['2fa'];
                            $_SESSION['verify'] = $row['verify'];
                            $_SESSION['notify'] = $row['notify'];
                            $_SESSION['online_status'] = 1;

                            $new_status = 1;
                            $sql = "UPDATE users SET online_status = ? WHERE id = ?";
                            $stmt = mysqli_stmt_init($conn);

                            if(!mysqli_stmt_prepare($stmt, $sql)) {
                                $_SESSION['error'] = 'There was an error, try again later';
                            } else {
                                mysqli_stmt_bind_param($stmt, "ss", $new_status, $_SESSION['UID']);
                                if(mysqli_stmt_execute($stmt)) {
                                    header("Location: dashboard.php");
                                }
                            }   

                            if($row['verify'] == 0) {
                                $_SESSION['loggedin'] = 1; 
                                $selector = bin2hex(random_bytes(8));
                                $token = random_bytes(32);

                                $url = "https://dall.ca/classes/verify.php?selector=" . $selector . "&validator=" . bin2hex($token);
                                $_SESSION['token'] = $token;

                                // Start of Delete Token
                                    $sql = "DELETE FROM email_tokens WHERE user_id=?";
                                    $stmt = mysqli_stmt_init($conn);
                                    mysqli_stmt_prepare($stmt, $sql);
                                    mysqli_stmt_bind_param($stmt, "s", $row['email']);
                                    mysqli_stmt_execute($stmt);     
                                // End of Delete Token


                                // Start of Insert New Token
                                    $sql = "INSERT INTO email_tokens (user_id, selector, token, code) VALUES (?, ?, ?, ?);";
                                    $stmt = mysqli_stmt_init($conn);

                                    mysqli_stmt_prepare($stmt, $sql);
                                    $hashedToken = password_hash($token, PASSWORD_DEFAULT);
                                    $code = substr(md5(uniqid(mt_rand(), true)) , 0, 8);
                                    mysqli_stmt_bind_param($stmt, "ssss", $row['email'], $selector, $hashedToken, $code);
                                    mysqli_stmt_execute($stmt);
                                // End of Insert New Token

                                // Email Script
                                    $to = $row['email'];

                                    $subject = "Verify your dallan.ca email";

                                    $message = "<p>The link to verify your email is below or use this code <strong>".$code."</strong></p>";
                                    $message .= "<p>Here is your email verification link: </br>";
                                    $message .= "<a href='" . $url . "'>liveChat link</a></p>";

                                    
                                    $result = sendMail($to,$subject,$message);
                                    if($result == 'error') {
                                        $_SESSION['error'] = 'Error! Could not send email, please try again later';
                                        header("Location: ../register.php");
                                    } else {
                                        $_SESSION['success'] = 'We have sent you an email! Please verify your email address by clicking the link in the email or enter the code';
                                        header("Location: ../verify.php");
                                    }


                            } else {

                                if($row['2fa'] == 0) { // if 2fa is off login normally
                                    $_SESSION['loggedin'] = 1; 

                                    // Remember me script
                                    require '../classes/remember_me.php';

                                    $_SESSION['success'] = 'Success! Welcome back '.$_SESSION['USERNAME'];                              
                                    if($row['first_login'] == "1") {   
                                        header("Location: ../welcome.php");  
                                    } else {
                                        header("Location: ../dashboard.php");
                                    }
                                } elseif($row['2fa'] == 1) { // 2fa pgp
                                    $selector = bin2hex(random_bytes(8));
                                    $token = random_bytes(32);

                                    $url = "https://dall.ca/classes/verify.php?selector=" . $selector . "&validator=" . bin2hex($token);
                                    $_SESSION['token'] = $token;
                                    $_SESSION['loggedin'] = 2; 

                                    if(isset($remember)) {
                                        $_SESSION['remember'] = 1;
                                    }

                                    $code = substr(md5(uniqid(mt_rand(), true)) , 0, 8);
                                    $expires = date("U") + 3600;
                                    // Start of Delete Token
                                        $sql = "DELETE FROM email_tokens WHERE user_id=?";
                                        $stmt = mysqli_stmt_init($conn);

                                        if(!mysqli_stmt_prepare($stmt, $sql)) {
                                            $_SESSION['error'] = 'There was an error, try again later';
                                            header("Location: ../forgot.php");
                                        } else {
                                            mysqli_stmt_bind_param($stmt, "s", $_SESSION['EMAIL']);
                                            mysqli_stmt_execute($stmt);
                                        }
                                    // End of Delete Token


                                    // Start of Insert New Token
                                        $sql = "INSERT INTO email_tokens (user_id, selector, token, code, expires) VALUES (?, ?, ?, ?, ?);";
                                        $stmt = mysqli_stmt_init($conn);

                                        if(!mysqli_stmt_prepare($stmt, $sql)) {
                                            $_SESSION['error'] = 'There was an error, try again later';
                                            header("Location: ../forgot.php");
                                        } else {
                                            $hashedToken = password_hash($token, PASSWORD_DEFAULT);
                                            mysqli_stmt_bind_param($stmt, "sssss", $_SESSION['EMAIL'], $selector, $hashedToken, $code, $expires);
                                            mysqli_stmt_execute($stmt);
                                        }
                                    // Start of Insert New Token

                                    // Email Script
                                    $to = $row['email'];

                                    $subject = "Verify your email";

                                    $message = "<p>The link to verify your email is below or use this code <strong>".$code."</strong></p>";
                                    $message .= "<p>Here is your email verification link: </br>";
                                    $message .= "<a href='" . $url . "'>liveChat link</a></p>";

                                
                                    $result = sendMail($to,$subject,$message);
                                    if($result == 'error') {
                                        $_SESSION['error'] = 'Error! Could not send email, please try again later';
                                        header("Location: ../register.php");
                                    } else {
                                        $_SESSION['success'] = 'This account has 2FA enabled, please submit the code or click the link found in your email';
                                        header("Location: ../verify.php");
                                    }
                                    
                                }
                            }
                        } else {
                            $_SESSION['loggedin'] = 0;
                            if(!isset($_SESSION["error"])) {
                                $_SESSION['error'] = 'Wrong password provided, try again';
                                header("Location: ../index.php?user=".$myusername);
                            }
                        }
                    }
                } else {
                    $_SESSION['loggedin'] = 0;
                    if(!isset($_SESSION["error"])) {
                      $_SESSION['error'] = 'This username or email is not found, try again';
                      header("Location: ../index.php");
                    }
                }
                
            }
        } else {
            // Not verified - show form error
            $_SESSION['error'] = 'Error! Captcha has failed, please try again';
            header("Location: ../index.php");
        }    
    } else {
        $_SESSION['error'] = 'Error! You do not have permission to access that request';
        header("Location: ../index.php");
        die();
    }
// End of Check Login Script







