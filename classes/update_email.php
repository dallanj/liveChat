<?php
require '../inc/db.php';
require '../classes/send_mail.php';
$id = $_SESSION['UID'];
$myemail = $_SESSION['EMAIL'];
    
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $password = mysqli_real_escape_string($conn,$_POST['password']);

    if(empty($email) || empty($password)) {
      $_SESSION['error'] = 'Please fill in all fields';
      header("Location: ../dashboard.php?update=email");
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Email is not a valid email address';
        header("Location: ../dashboard.php?update=email");
    } else if ($email == $myemail) {
        $_SESSION['error'] = "You're already using this email, pick a different one";
        header("Location: ../dashboard.php?update=email");
    } else {

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows == 1) {
            $_SESSION['error'] = 'Email already in use, try something else';
            header("Location: ../dashboard.php?update=email");
        }
        if($stmt->num_rows == 0) {

            $sql = "SELECT * FROM users WHERE id=?";
            $stmt = mysqli_stmt_init($conn);

            if(!mysqli_stmt_prepare($stmt, $sql)) {
                $_SESSION['error'] = 'There was an error, try again later';
            } else {
                mysqli_stmt_bind_param($stmt, "s", $id);
                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);
                while($row = mysqli_fetch_assoc($result)) {
                    if(password_verify($password, $row['password'])) {

                        $selector = bin2hex(random_bytes(8));
                        $authenticator = random_bytes(33);
                        $token = random_bytes(32);

                        $url = "https://dall.ca/classes/verify_updated_email.php?selector=" . $selector . "&validator=" . bin2hex($token);
                        $_SESSION['token'] = $token;
                        $_SESSION['tokenEmail'] = $email;
                        $expires = date("U") + 3600;

                        // Start of Delete Token
                        $sql = "DELETE FROM email_tokens WHERE user_id=?";
                        $stmt = mysqli_stmt_init($conn);
                        mysqli_stmt_prepare($stmt, $sql);
                        mysqli_stmt_bind_param($stmt, "s", $email);
                        mysqli_stmt_execute($stmt);     
                        // End of Delete Token


                        // Start of Insert New Token
                        $sql = "INSERT INTO email_tokens (user_id, selector, token, code, expires) VALUES (?, ?, ?, ?, ?);";
                        $stmt = mysqli_stmt_init($conn);

                        mysqli_stmt_prepare($stmt, $sql);
                        $hashedToken = password_hash($token, PASSWORD_DEFAULT);
                        $code = substr(md5(uniqid(mt_rand(), true)) , 0, 8);
                        mysqli_stmt_bind_param($stmt, "sssss", $email, $selector, $hashedToken, $code, $expires);
                        mysqli_stmt_execute($stmt);
                        // End of Insert New Token

                        $to = $email;

                        $subject = "Verify your email";

                        $message = "<p>The link to verify your email is below or use this code <strong>".$code."</strong></p>";
                        $message .= "<p>Here is your email verification link:</br>";
                        $message .= "<a href='" . $url . "'>liveChat link</a></p>";

                        $result = sendMail($to,$subject,$message);
                        if($result == 'error') {
                            $_SESSION['error'] = 'Error! Could not send email, please try again later';
                            header("Location: ../dashboard.php?update=email");
                        } else {
                            $_SESSION['success'] = 'We have sent you an email! Please verify your email address by clicking the link in the email or enter the code';
                            header("Location: ../dashboard.php?verify=email");
                        }

                    } else {
                        $_SESSION['error'] = 'Wrong password provided';
                        header("Location: ../dashboard.php?update=email&mail=".$email);
                    }
                }
            }
        }
    }

} else {
    $_SESSION['error'] = 'Error! You do not have permission to access that request';
    header("Location: ../index.php");
    die();
}
//header("Location: ../dashboard.php?update=email");





