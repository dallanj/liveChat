<?php
include('../../inc/db.php');

if($_SERVER['REQUEST_METHOD'] === 'POST'){
	$msg = mysqli_real_escape_string($conn,$_POST['msg']);
	$sender_id = mysqli_real_escape_string($conn,$_POST['sender_id']);
	$receiver_id = mysqli_real_escape_string($conn,$_POST['receiver_id']);
	if(strlen($msg) < 2) {
		$alert['message'] = 'Error! Message must be greater than 2 characters';
        $alert['type'] = 'error';

		// header("Location: ".$_SERVER['HTTP_REFERER']);
	} else {
		$sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?);";
	  	$stmt = mysqli_stmt_init($conn);
	  	if(!mysqli_stmt_prepare($stmt, $sql)) {
	    	$alert['message'] = 'Error! The server is having trouble creating a note, try again later';
        	$alert['type'] = 'error';
	  	} else {
	  		mysqli_stmt_bind_param($stmt, "sss", $sender_id, $receiver_id, $msg);
	  		mysqli_stmt_execute($stmt);

	  	} 	
	}
	
	echo json_encode($alert);
}