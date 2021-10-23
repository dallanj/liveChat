<?php
require '../inc/db.php';


// Start of Password Reset with Prepared Statements
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recaptcha_response'])) {
		// Build POST request:
	    require '../classes/recaptcha.php';

	    // Take action based on the score returned:
	    if ($recaptcha->score >= 0.5) {
	        // Verified - send email
	        $selector = $_POST['selector'];
			$validator = $_POST['validator'];
			$password = $_POST['password'];
			$hash = password_hash($password, PASSWORD_DEFAULT);
			$confirm = $_POST['confirm'];

			if(empty($password) || empty($confirm)) {
	            $_SESSION['error'] = 'Please fill out all details';
	            header("Location: ".$_SERVER['HTTP_REFERER']);
	        }
			else if(strlen($password) < 8) {
	            $_SESSION['error'] = 'Your password needs to be a minimum of 8 characters';
	            header("Location: ".$_SERVER['HTTP_REFERER']);
	        }
	        else if(preg_match("/[A-Z]/", $password) === 0 || preg_match("/[a-z]/", $password) === 0) {
	            $_SESSION['error'] = 'Your password must contain atleast one uppercase and lowercase letter';
	            header("Location: ".$_SERVER['HTTP_REFERER']);
	        } else if(preg_match("/[0-9]/", $password) === 0) {
	            $_SESSION['error'] = 'Your password must contain atleast one number';
	            header("Location: ".$_SERVER['HTTP_REFERER']);
	        } else if ((!ctype_alnum($password)) || (!ctype_alnum($confirm))) {
	            $_SESSION['error'] = 'Passwords may only contain letters and numbers';
	            header("Location: ".$_SERVER['HTTP_REFERER']);
	        }
	        else if ((!ctype_alnum($password)) || (!ctype_alnum($confirm))) {
	            $_SESSION['error'] = 'Passwords may only contain letters and numbers';
	            header("Location: ".$_SERVER['HTTP_REFERER']);
	        }
	        else if($password != $confirm) {
	            $_SESSION['error'] = 'Your passwords did not match';
	            header("Location: ".$_SERVER['HTTP_REFERER']);
	        } else {

		        $currentDate = date("U");

		        $sql = "SELECT * FROM pwdReset WHERE pwdResetSelector=? AND pwdResetExpires >= ?";
		        $stmt = mysqli_stmt_init($conn);

			    if(!mysqli_stmt_prepare($stmt, $sql)) {
			    	$_SESSION['error'] = 'There was an error, try again later';
			        header("Location: ../forgot.php");
			    } else {
			       	mysqli_stmt_bind_param($stmt, "ss", $selector, $currentDate);
			       	mysqli_stmt_execute($stmt);

			       	$result = mysqli_stmt_get_result($stmt);
			      	if(!$row = mysqli_fetch_assoc($result)) {
			      		$_SESSION['error'] = 'Error! You need to resend a new password reset request';
			           	header("Location: ../forgot.php");
			       	} else {

			       		$tokenBin = hex2bin($validator);
			       		$tokenCheck = password_verify($tokenBin, $row['pwdResetToken']);

			       		if($tokenCheck === false) {
			       			$_SESSION['error'] = 'Error! You need to resend a new password reset request';
				           	header("Location: ../forgot.php");
			       		} else if($tokenCheck === true) {
			       			$tokenEmail = $row['pwdResetUser'];

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
						       		$sql = "UPDATE users SET password=? WHERE email=?";
						       		$stmt = mysqli_stmt_init($conn);

								    if(!mysqli_stmt_prepare($stmt, $sql)) {
								    	$_SESSION['error'] = 'There was an error, try again later';
								        header("Location: ../forgot.php");
								    } else {
								    	mysqli_stmt_bind_param($stmt, "ss", $hash, $tokenEmail);
								    	mysqli_stmt_execute($stmt);

								    	$sql = "DELETE FROM pwdReset WHERE pwdResetUser=?";
							       		$stmt = mysqli_stmt_init($conn);

									    if(!mysqli_stmt_prepare($stmt, $sql)) {
									    	$_SESSION['error'] = 'There was an error, try again later';
									        header("Location: ../forgot.php");
									    } else {
									    	mysqli_stmt_bind_param($stmt, "s", $tokenEmail);
									    	mysqli_stmt_execute($stmt);
									    	$_SESSION['success'] = 'Success! You have reset your password, you may now login';
								        	header("Location: ../index.php");
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
            header("Location: ../change_password.php");
	    }
	} else {
        $_SESSION['error'] = 'Error! You do not have permission to access that request';
        header("Location: ../index.php");
        die();
    }
// End of Password Reset with Prepared Statements