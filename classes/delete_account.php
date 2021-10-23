<?php
require '../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['loggedin'] === 1) {

    $id = $_SESSION['UID'];
    $email = $_SESSION['EMAIL'];
    $password = mysqli_real_escape_string($conn,$_POST['password']);

    if(empty($password)) {
        $_SESSION['error'] = 'Please fill in your password';
    } else {
        $sql = "SELECT * FROM users WHERE id=?";
        $stmt = mysqli_stmt_init($conn);
        mysqli_stmt_prepare($stmt, $sql);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        while($row = mysqli_fetch_assoc($result)) {
            if(password_verify($password, $row['password'])) {

                $sql = "DELETE FROM pwdReset WHERE pwdResetUser=?";
                $stmt = mysqli_stmt_init($conn);
                mysqli_stmt_prepare($stmt, $sql);
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);

                $sql = "DELETE FROM auth_tokens WHERE userid=?";
                $stmt = mysqli_stmt_init($conn);
                mysqli_stmt_prepare($stmt, $sql);
                mysqli_stmt_bind_param($stmt, "s", $id);
                mysqli_stmt_execute($stmt);

                $sql = "DELETE FROM email_tokens WHERE user_id=?";
                $stmt = mysqli_stmt_init($conn);
                mysqli_stmt_prepare($stmt, $sql);
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);

                $sql = "DELETE FROM users WHERE id=?";
                $stmt = mysqli_stmt_init($conn);
                mysqli_stmt_prepare($stmt, $sql);
                mysqli_stmt_bind_param($stmt, "s", $id);
                mysqli_stmt_execute($stmt);

                // Delete cookie by setting it before current time (expired)
                if(isset($_COOKIE['remember'])) {
                    setcookie("remember", "", time() - 7200, '/');
                }

                // Unset all sessions without using session_destroy() so we can display a logout message
                unset($_SESSION['loggedin']);
                unset($_SESSION['LOGGEDIN_ADMIN']);
                unset($_SESSION['USERNAME']);
                unset($_SESSION['EMAIL']);
                unset($_SESSION['token']);
                unset($_SESSION['verify']);
                unset($_SESSION['security']);
                unset($_SESSION['first_login']);
                unset($_SESSION['TYPE']);
                unset($_SESSION['UID']);
                unset($_SESSION['notify']);
                unset($_SESSION['tokenEmail']);

                    
                $_SESSION['success'] = 'You have successfully deleted your account';
                header("Location: ../classes/logout.php?delete=true");
            } else {
                $_SESSION['error'] = 'Wrong password provided'; 
            }
        }
    }
    header("Location: ../dashboard.php?update=delete"); 
} else {
    $_SESSION['error'] = 'Error! You do not have permission to access that request';
    header("Location: ../index.php");
    die();
}