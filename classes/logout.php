<?php
include '../inc/db.php';

// Delete cookie auth token
$sql = "DELETE FROM auth_tokens WHERE userid=?";
$stmt = mysqli_stmt_init($conn);
if(mysqli_stmt_prepare($stmt, $sql)) {
	mysqli_stmt_bind_param($stmt, "s", $_SESSION['UID']);
	mysqli_stmt_execute($stmt);
}
// Delete cookie by setting it before current time (expired)
if(isset($_COOKIE['remember'])) {
	setcookie("remember", "", time() - 7200, '/');
}
// Update online status to offline
$new_status = 0;
$sql = "UPDATE users SET online_status = ? WHERE id = ?";
$stmt = mysqli_stmt_init($conn);

if(!mysqli_stmt_prepare($stmt, $sql)) {
    $_SESSION['error'] = 'There was an error, try again later';
} else {
    mysqli_stmt_bind_param($stmt, "ss", $new_status, $_SESSION['UID']);
 	mysqli_stmt_execute($stmt);
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
unset($_SESSION['online_status']);
unset($_SESSION['active_now']);
unset($_SESSION['friend_list']);

unset($_SESSION['friends']);
unset($_SESSION['account']);
unset($_SESSION['requests']);

if(isset($_GET['delete'])) {
	$_SESSION['info'] = 'You have successfully deleted your account';
} else {
	$_SESSION['info'] = 'You have successfully logged out';
}
header("Location: ../index.php");