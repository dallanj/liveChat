<?php
require '../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recaptcha_response'])) {
    // Build POST request:
    require '../classes/recaptcha.php';

    // Take action based on the score returned:
    if ($recaptcha->score >= 0.5) {
        // Verified
        if($_SESSION['loggedin'] == 1 && $_SESSION['first_login'] == 1) {
            $newFirstLogin = 0;
            $sql = "UPDATE users SET first_login = ? WHERE id=?";
            $stmt = mysqli_stmt_init($conn);

            if(!mysqli_stmt_prepare($stmt, $sql)) {
                $_SESSION['error'] = 'There was an error, try again later';
                header("Location: ../welcome.php");
            } else {
                mysqli_stmt_bind_param($stmt, "ss", $newFirstLogin, $_SESSION['UID']);
                mysqli_stmt_execute($stmt);
                $_SESSION['first_login'] = $newFirstLogin;
                header("Location: ../dashboard.php");
            }
        } else {
            $_SESSION['error'] = 'Error! You do not have permission to do that action';
            header("Location: ../index.php");
        }
    } else {
        // Not verified - show form error
        $_SESSION['error'] = 'Error! Captcha has failed, please try again';
        header("Location: ../welcome.php");
    }
} else {
    $_SESSION['error'] = 'Error! You do not have permission to access that request';
    header("Location: ../index.php");
    die();
}