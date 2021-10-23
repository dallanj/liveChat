<?php
/*
This class is registration
*/
require '../inc/db.php';
require '../classes/send_mail.php';

// Start of Registration with Prepared Statements

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recaptcha_response'])) {
        // Build POST request:
        require '../classes/recaptcha.php';

        // Take action based on the score returned:
        if ($recaptcha->score >= 0.5) {
            // Verified - send email
            $myusername = mysqli_real_escape_string($conn,$_POST['username']);
            $myemail = mysqli_real_escape_string($conn,$_POST['email']);
            $mypassword = mysqli_real_escape_string($conn,$_POST['password']);
            $confirm = mysqli_real_escape_string($conn,$_POST['confirm']);
            $hash = password_hash($mypassword, PASSWORD_DEFAULT);
            $notify = $_POST['notify'];
            $agree = $_POST['agree'];

            // Include profanity words list
            $profanity = file_get_contents('../inc/list.txt');
            $profanity = preg_split("/\\r\\n|\\r|\\n/", $profanity);

            if(empty($myusername) || empty($myemail) || empty($mypassword) || empty($confirm)) {
                $_SESSION['error'] = 'Please fill out all details';
                header("Location: ../register.php");
            }
            else if (!ctype_alnum($myusername)) {
                $_SESSION['error'] = 'Username may only contain letters and numbers';
                header("Location: ../register.php?mail=".$myemail);
            }
            else if (strlen($myusername) < 3) {
                $_SESSION['error'] = 'Username must be atleast 3 characters';
                header("Location: ../register.php?mail=".$myemail);
            }
            else if(in_array($myusername,$profanity)) {
                $_SESSION['error'] = 'Username contains profanity, please choose a different username';
                header("Location: ../register.php?mail=".$myemail);
            }
            else if (!filter_var($myemail, FILTER_VALIDATE_EMAIL) && !empty($myemail)) {
                $_SESSION['error'] = 'Email is not a valid email address';
                header("Location: ../register.php?user=".$myusername);
            }
            else if(strlen($mypassword) < 8) {
                $_SESSION['error'] = 'Your password needs to be a minimum of 8 characters';
                header("Location: ../register.php?user=".$myusername."&mail=".$myemail);
            }
            else if(preg_match("/[A-Z]/", $mypassword) === 0 || preg_match("/[a-z]/", $mypassword) === 0) {
                $_SESSION['error'] = 'Your password must contain atleast one uppercase and lowercase letter';
                header("Location: ../register.php?user=".$myusername."&mail=".$myemail);
            } else if(preg_match("/[0-9]/", $mypassword) === 0) {
                $_SESSION['error'] = 'Your password must contain atleast one number';
                header("Location: ../register.php?user=".$myusername."&mail=".$myemail);
            } else if ((!ctype_alnum($mypassword)) || (!ctype_alnum($confirm))) {
                $_SESSION['error'] = 'Passwords may only contain letters and numbers';
                header("Location: ../register.php?user=".$myusername."&mail=".$myemail);
            }
            else if ((!ctype_alnum($mypassword)) || (!ctype_alnum($confirm))) {
                $_SESSION['error'] = 'Passwords may only contain letters and numbers';
                header("Location: ../register.php?user=".$myusername."&mail=".$myemail);
            }
            else if($mypassword != $confirm) {
                $_SESSION['error'] = 'Your passwords did not match';
                header("Location: ../register.php?user=".$myusername."&mail=".$myemail);
            } 
            else if(!$agree) {
                $_SESSION['error'] = 'You need to agree to the terms and conditions to register an account';
                header("Location: ../register.php?user=".$myusername."&mail=".$myemail);
            } else {
      
                if(isset($notify)) {
                    $notify = 1;
                } else {
                    $notify = 0;
                }
                if(isset($agree)) {
                    $agree = 1;
                } else {
                    $agree = 0;
                }


                $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
                $stmt->bind_param("ss", $myusername, $myemail);
                $stmt->execute();
                $stmt->store_result();
                if($stmt->num_rows == 1) {
                    $_SESSION['error'] = 'Username or email already in use, try something else';
                    header("Location: ../register.php");
                }
                if($stmt->num_rows == 0 && ($mypassword == $confirm)) {
                    $stmt = $conn->prepare("INSERT INTO users (username, password, email, agree, notify) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssii", $myusername, $hash, $myemail, $agree, $notify);    
                    if($stmt->execute() === TRUE) {
                        $_SESSION['EMAIL'] = $myemail;
                        $selector = bin2hex(random_bytes(8));
                        $token = random_bytes(32);

                        $url = "https://dall.ca/classes/verify.php?selector=" . $selector . "&validator=" . bin2hex($token);
                        $_SESSION['token'] = $token;

                        // Start of Delete Token
                            $sql = "DELETE FROM email_tokens WHERE user_id=?";
                            $stmt = mysqli_stmt_init($conn);
                            mysqli_stmt_prepare($stmt, $sql);
                            mysqli_stmt_bind_param($stmt, "s", $myemail);
                            mysqli_stmt_execute($stmt);     
                        // End of Delete Token


                        // Start of Insert New Token
                            $sql = "INSERT INTO email_tokens (user_id, selector, token, code) VALUES (?, ?, ?, ?);";
                            $stmt = mysqli_stmt_init($conn);

                            mysqli_stmt_prepare($stmt, $sql);
                            $hashedToken = password_hash($token, PASSWORD_DEFAULT);
                            $code = substr(md5(uniqid(mt_rand(), true)) , 0, 8);
                            mysqli_stmt_bind_param($stmt, "ssss", $myemail, $selector, $hashedToken, $code);
                            mysqli_stmt_execute($stmt);
                        // End of Insert New Token

                        $to = $myemail;

                        $subject = "Verify your email";

                        $message = "<p>Thank you for registering. The link to verify your email is below or use this code <strong>".$code."</strong></p>";
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

                    }
                }
            }
        } else {
            // Not verified - show form error
            $_SESSION['error'] = 'Error! Captcha has failed, please try again';
            header("Location: ../register.php");
        }

    } else {
        $_SESSION['error'] = 'Error! You do not have permission to access that request';
        header("Location: ../index.php");
        die();
    }

// End of Registration with Prepared Statements





