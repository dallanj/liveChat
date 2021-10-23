<?php
require '../inc/db.php';
require '../classes/send_mail.php';


// Start of Password Reset with Prepared Statements
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recaptcha_response'])) {
    	// Build POST request:
	    require '../classes/recaptcha.php';

	    // Take action based on the score returned:
	    if ($recaptcha->score >= 0.5) {
	        // Verified - send email
	        $myuser = $_POST['username'];

	    	if(empty($myuser)) {
	            $_SESSION['error'] = 'Please fill in your email address or username';
	            header("Location: ../forgot.php");
	        } else {

	        	$selector = bin2hex(random_bytes(8));
	        	$token = random_bytes(32);

	        	$url = "https://dall.ca/classes/change_password.php?selector=" . $selector . "&validator=" . bin2hex($token);

	        	$expires = date("U") + 1800;


	        	$sql = "SELECT * FROM users WHERE username = ? OR email = ?";
	            $stmt = mysqli_stmt_init($conn);
	            if(!mysqli_stmt_prepare($stmt, $sql)) {
	                $_SESSION['error'] = 'The server is having trouble logging in, try again later';
	                header("Location: ../index.php");
	            } else {
	                mysqli_stmt_bind_param($stmt, "ss", $myuser, $myuser);
	                mysqli_stmt_execute($stmt);

	                $result = mysqli_stmt_get_result($stmt);
	                $count = mysqli_num_rows($result);

	                if($count == 1) { 

	                    $row = mysqli_fetch_assoc($result);
	                    $myemail = $row['email'];
			        	// Start of Delete Token
				        	$sql = "DELETE FROM pwdReset WHERE pwdResetUser=?";
				        	$stmt = mysqli_stmt_init($conn);

				        	if(!mysqli_stmt_prepare($stmt, $sql)) {
				        		$_SESSION['error'] = 'There was an error, try again later';
				                header("Location: ../forgot.php");
				        	} else {
				        		mysqli_stmt_bind_param($stmt, "s", $myemail);
				        		mysqli_stmt_execute($stmt);
				        	}
			        	// End of Delete Token


			        	// Start of Insert New Token
				        	$sql = "INSERT INTO pwdReset (pwdResetUser, pwdResetSelector, pwdResetToken, pwdResetExpires) VALUES (?, ?, ?, ?);";
				        	$stmt = mysqli_stmt_init($conn);

				        	if(!mysqli_stmt_prepare($stmt, $sql)) {
				        		$_SESSION['error'] = 'There was an error, try again later';
				                header("Location: ../forgot.php");
				        	} else {
				        		$hashedToken = password_hash($token, PASSWORD_DEFAULT);
				        		mysqli_stmt_bind_param($stmt, "ssss", $myemail, $selector, $hashedToken, $expires);
				        		mysqli_stmt_execute($stmt);
				        	}
				        

				        	mysqli_stmt_close($stmt);
				        	mysqli_close($conn);

				        	$to = $myemail;

				        	$subject = "Reset your QuickNotes password";

				        	$message = "<p>We have received a password reset request. The link to reset your password to make this request is below, if you did not make this request. Please ignore this email.</p>";
				        	$message .= "<p>Here is your password reset link:</br> ";
				        	$message .= "<a href='" . $url . "'>liveChat link</a></p>";

	                        $result = sendMail($to,$subject,$message);
	                        if($result == 'error') {
	                            $_SESSION['error'] = 'Error! Could not send email, please try again later';
	                            header("Location: ../forgot.php");
	                        } else {
	                            $_SESSION['success'] = 'Success! A reset password request token was sent to your email';
	                            header("Location: ../forgot.php");
	                        }

				        // END of Insert New Token
				    } else {
				   		$_SESSION['error'] = 'Username or email not found, try again';
	        			header("Location: ../forgot.php");
				    }
				}
			}
	    } else {
	        // Not verified - show form error
	        $_SESSION['error'] = 'Error! Captcha has failed, please try again';
            header("Location: ../forgot.php");
	    }
    } else {
    	$_SESSION['error'] = 'Error! You do not have permission to access that request';
        header("Location: ../index.php");
        die();
    }