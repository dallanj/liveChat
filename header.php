<?php
include("inc/db.php");
?>

<head>
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.13.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="./inc/styleChat.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet"> 
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>

<?php
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_stmt_init($conn);
if(!mysqli_stmt_prepare($stmt, $sql)) {
    $_SESSION['error'] = 'The server is having trouble logging in, try again later';
} else {
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['UID']);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    while($row = mysqli_fetch_assoc($result)) {
    	$friend_list = explode(',',$row['friends']);
    	$last_active = $row['last_active'];
        $myUsername = $row['username'];
        $myEmail = $row['email'];
   	}
}

$current_time = time();

// update last active
$sql = "UPDATE users SET last_active = ? WHERE id = ?";
$stmt = mysqli_stmt_init($conn);

if(!mysqli_stmt_prepare($stmt, $sql)) {
    $_SESSION['error'] = 'There was an error, try again later';
} else {
    mysqli_stmt_bind_param($stmt, "ss", $current_time, $_SESSION['UID']);
    mysqli_stmt_execute($stmt);
}

if(time() > $last_active + 1) {
	$_SESSION['active_now'] = ' (active now)';
} else {
	$_SESSION['active_now'] = ' (offline)';
}
?>

