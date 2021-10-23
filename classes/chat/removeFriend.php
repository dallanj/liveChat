<?php
include('../../inc/db.php');

if($_SERVER['REQUEST_METHOD'] === 'POST'){
	$uid = mysqli_real_escape_string($conn,$_POST['uid']);
	if(empty($uid)) {
		$alert['message'] = 'The server is having trouble connecting, try again later';
        $alert['type'] = 'error';
	} else {

		// delete friend from ex-friends list

		$sql = "SELECT * FROM users WHERE id = ?";
		$stmt = mysqli_stmt_init($conn);
		if(!mysqli_stmt_prepare($stmt, $sql)) {
		   $alert['message'] = 'The server is having trouble connecting, try again later';
            $alert['type'] = 'error';
		} else {
		    mysqli_stmt_bind_param($stmt, "s", $uid);
		    mysqli_stmt_execute($stmt);

		    $result = mysqli_stmt_get_result($stmt);
		    $row = mysqli_fetch_assoc($result);

		    $friends_uid = $row['username'];
	    	$ex_friend_list = explode(',',$row['friends']);

	    	foreach($ex_friend_list as $key => $friend) 
			{ 
			    if($friend == $_SESSION['UID']) 
			    { 
			        unset($ex_friend_list[$key]); 
			    } 
			} 
			$ex_friend_list = implode(',', $ex_friend_list);

			$sql = "UPDATE users SET friends = ? WHERE id = ?";
			$stmt = mysqli_stmt_init($conn);

			if(!mysqli_stmt_prepare($stmt, $sql)) {
			    $alert['message'] = 'The server is having trouble connecting, try again later';
                $alert['type'] = 'error';
			} else {
			    mysqli_stmt_bind_param($stmt, "ss", $ex_friend_list, $uid);
			    mysqli_stmt_execute($stmt);
			}
		   	
		}

		// delete friend from users list

		$sql = "SELECT * FROM users WHERE id = ?";
		$stmt = mysqli_stmt_init($conn);
		if(!mysqli_stmt_prepare($stmt, $sql)) {
		    $alert['message'] = 'The server is having trouble connecting, try again later';
            $alert['type'] = 'error';
		} else {
		    mysqli_stmt_bind_param($stmt, "s", $_SESSION['UID']);
		    mysqli_stmt_execute($stmt);

		    $result = mysqli_stmt_get_result($stmt);
		    $row = mysqli_fetch_assoc($result);
	    	$my_friend_list = explode(',',$row['friends']);

	    	foreach($my_friend_list as $key => $friend) 
			{ 
			    if($friend == $uid) 
			    { 
			        unset($my_friend_list[$key]); 
			    } 
			} 

			$my_friend_list = implode(',', $my_friend_list);

			$sql = "UPDATE users SET friends = ? WHERE id = ?";
			$stmt = mysqli_stmt_init($conn);

			if(!mysqli_stmt_prepare($stmt, $sql)) {
			    $alert['message'] = 'The server is having trouble connecting, try again later';
                $alert['type'] = 'error';
			} else {
			    mysqli_stmt_bind_param($stmt, "ss", $my_friend_list, $_SESSION['UID']);
			    mysqli_stmt_execute($stmt);
			}

		   	
		}

		// delete conversation between the two

		$sql = "DELETE FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)";
        $stmt = mysqli_stmt_init($conn);
        mysqli_stmt_prepare($stmt, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $_SESSION['UID'], $uid, $uid, $_SESSION['UID']);
        mysqli_stmt_execute($stmt);

		$alert['message'] = 'You have removed '.$friends_uid.' as a friend';
        $alert['type'] = 'success';
	}
	echo json_encode($alert);
}