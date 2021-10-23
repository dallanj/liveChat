<?php
// Start of Re-Authenticating on Page Load
    if(empty($_SESSION['UID']) && !empty($_COOKIE['remember'])) {
        list($selector, $authenticator) = explode(':', $_COOKIE['remember']);

        $sql = "SELECT * FROM auth_tokens WHERE selector=?";
        $stmt = mysqli_stmt_init($conn);

        if(!mysqli_stmt_prepare($stmt, $sql)) {
            print 'There was an error, try again later';
        } else {
            mysqli_stmt_bind_param($stmt, "s", $selector);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            if (hash_equals($row['token'], hash('sha256', base64_decode($authenticator)))) {
		        $_SESSION['UID'] = $row['userid'];  
        	    $id = $row['userid'];
           	    $_SESSION['loggedin'] = 1; 
                $sql = "SELECT * FROM users WHERE id=?";
                $stmt = mysqli_stmt_init($conn);

	            if(!mysqli_stmt_prepare($stmt, $sql)) {
	                print 'There was an error, try again later';
	            } else {
	                mysqli_stmt_bind_param($stmt, "s", $id);
	                mysqli_stmt_execute($stmt);
	                $result = mysqli_stmt_get_result($stmt);
	                $row = mysqli_fetch_assoc($result);
	                $_SESSION['EMAIL'] = $row['email'];
	                $_SESSION['USERNAME'] = $row['username'];
	                $_SESSION['TYPE'] = $row['type'];
                    $_SESSION['first_login'] = $row['first_login'];
                    $_SESSION['security'] = $row['2fa'];
                    $_SESSION['verify'] = $row['verify'];
                    if($row['type'] == 2) {
                        $_SESSION['LOGGEDIN_ADMIN'] = 1;
                    }
	            }
                $_SESSION['success'] = 'Success! Welcome back '.$_SESSION['USERNAME']; 
            }
        }
    }

// Obtain user information on page load
    if(!isset($_SESSION['loggedin'])) {
        unset($_SESSION['LOGGEDIN_ADMIN']);
        unset($_SESSION['USERNAME']);
        //unset($_SESSION['EMAIL']);
        //unset($_SESSION['token']);
        unset($_SESSION['verify']);
        unset($_SESSION['security']);
        unset($_SESSION['first_login']);
        unset($_SESSION['TYPE']);
        unset($_SESSION['UID']);
        unset($_SESSION['notify']);
        unset($_SESSION['tokenEmail']);
    }
    
    if($_SESSION['loggedin'] === 1) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql)) {
            $_SESSION['error'] = 'The server is having trouble connecting to the database, try again later';
        } else {
            mysqli_stmt_bind_param($stmt, "s", $_SESSION['UID']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            while($row = mysqli_fetch_assoc($result)) {
                $_SESSION['UID'] = $row['id'];
                $_SESSION['USERNAME'] = $row['username'];
                $_SESSION['EMAIL'] = $row['email'];
                $_SESSION['TYPE'] = $row['type'];
                $_SESSION['first_login'] = $row['first_login'];
                $_SESSION['security'] = $row['2fa'];
                $_SESSION['verify'] = $row['verify'];
                $_SESSION['notify'] = $row['notify'];
            }
        }
    }