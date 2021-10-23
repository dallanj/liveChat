<?php
include('../../inc/db.php');

if($_SERVER['REQUEST_METHOD'] === 'POST') {

	// Fetch user account data and store in data array under account
	$sql = "SELECT * FROM users WHERE id=?";
	$stmt = mysqli_stmt_init($conn);

	if(!mysqli_stmt_prepare($stmt, $sql)) {
	    $_SESSION['error'] = 'There was an error, try again later';
	} else {
	    mysqli_stmt_bind_param($stmt, "s", $_SESSION['UID']);
	    mysqli_stmt_execute($stmt);

	    $result = mysqli_stmt_get_result($stmt);
	    $row_count = mysqli_num_rows($result);
	    while($row = mysqli_fetch_assoc($result)) {

	    	//for future turn this into a function
	    	$last_active = $row['last_active'];
        	if($row['online_status'] == 1 && time() < $last_active+60) {
				$status_color = 'green';
			} elseif($row['online_status'] == 1 && time() >= $last_active+60) {
				$status_color = 'orange';
			} elseif($row['online_status'] == 0) {
				$status_color = '#eee';
			}

	    	$friends = explode(',',$row['friends']);

	    	$data['account']['id'] = $row['id'];
			$data['account']['username'] = $row['username'];
			$data['account']['email'] = $row['email'];
			$data['account']['last_active'] = $row['last_active'];
			$data['account']['online_status'] = $row['online_status'];
			$data['account']['status_color'] = $status_color;
			$_SESSION['account'] = $data['account'];

			$_SESSION['EMAIL'] = $row['email'];
			$_SESSION['USERNAME'] = $row['username'];
			$_SESSION['last_active'] = $row['last_active'];
			$_SESSION['online_status'] = $row['online_status'];
			$_SESSION['status_color'] = $status_color;
			$_SESSION['security'] = $row['2fa'];
			$_SESSION['notify'] = $row['notify'];
	    }
	}

	// Fetch friend list data and store in data array under friends
	foreach($friends as $friend) {
		$sql = "SELECT * FROM users WHERE id=?";
		$stmt = mysqli_stmt_init($conn);

		if(!mysqli_stmt_prepare($stmt, $sql)) {
		    $_SESSION['error'] = 'There was an error, try again later';
		} else {
		    mysqli_stmt_bind_param($stmt, "s", $friend);
		    mysqli_stmt_execute($stmt);

		    $result = mysqli_stmt_get_result($stmt);
	        while($row = mysqli_fetch_assoc($result)) {
	        	$sql_unreadMsg = "SELECT * FROM messages WHERE receiver_id=? AND sender_id=?";
				$stmt_unreadMsg = mysqli_stmt_init($conn);

				if(!mysqli_stmt_prepare($stmt_unreadMsg, $sql_unreadMsg)) {
				    $_SESSION['error'] = 'There was an error, try again later';
				} else {
				    mysqli_stmt_bind_param($stmt_unreadMsg, "ss", $_SESSION['UID'], $friend);
				    mysqli_stmt_execute($stmt_unreadMsg);

				    $result_unreadMsg = mysqli_stmt_get_result($stmt_unreadMsg);
				    $row_count = mysqli_num_rows($result_unreadMsg);
			        while($row_unreadMsg = mysqli_fetch_assoc($result_unreadMsg)) {
			        	
			        	if($row_unreadMsg['status']==0) {
			        		$numUnread++;
							$friend_username = '<strong>'.$row['username'].'</strong> <span class="msgCount">'.$numUnread.'</span>';
							
						} else {
							$friend_username = $row['username'];
						}

					}
					if($row_count<1) {
						$friend_username = $row['username'];
					}
				}
	        	$last_active = $row['last_active'];
	        	if($row['online_status'] == 1 && time() < $last_active+60) {
					$active_now = ' (active now)';
					$status_color = 'green';

				} elseif($row['online_status'] == 1 && time() >= $last_active+60) {
					$active_now = ' (away)';
					$status_color = 'orange';
				} elseif($row['online_status'] == 0) {
					$active_now = ' (offline)';
					$status_color = '#eee';
				}

				$data['friends'][$friend]['id'] = $row['id'];
				$data['friends'][$friend]['username'] = $friend_username;
				$data['friends'][$friend]['last_active'] = $row['last_active'];
				$data['friends'][$friend]['online_status'] = $row['online_status'];
				$data['friends'][$friend]['active_now'] = $active_now;
				$data['friends'][$friend]['status_color'] = $status_color;
				$_SESSION['friends'] = $data['friends'];
				
	        }

		}
	}


	usort($data['friends'], function($a, $b) {
    	return $b['online_status'] <=> $a['online_status'];
	});

	// Fetch incoming friend requests 
	$sql = "SELECT * FROM friend_request WHERE receiver_id = ?";
	$stmt = mysqli_stmt_init($conn);
	if(!mysqli_stmt_prepare($stmt, $sql)) {
	    $_SESSION['error'] = 'The server is having trouble logging in, try again later';
	} else {
	    mysqli_stmt_bind_param($stmt, "s", $_SESSION['UID']);
	    mysqli_stmt_execute($stmt);

	    $result = mysqli_stmt_get_result($stmt);
	    $count = mysqli_num_rows($result);

	    if($count >= 1) { 
	    	while($row = mysqli_fetch_assoc($result)) {
	    		$sql1 = "SELECT * FROM users WHERE id = ?";
				$stmt1 = mysqli_stmt_init($conn);
				if(!mysqli_stmt_prepare($stmt1, $sql1)) {
				    $_SESSION['error'] = 'The server is having trouble logging in, try again later';
				} else {
				    mysqli_stmt_bind_param($stmt1, "s", $row['sender_id']);
				    mysqli_stmt_execute($stmt1);

				    $result1 = mysqli_stmt_get_result($stmt1);

			    	while($row1 = mysqli_fetch_assoc($result1)) {
			           	$data['requests'][$row['id']]['id'] = $row1['id'];
						$data['requests'][$row['id']]['username'] = $row1['username'];
						$data['requests'][$row['id']]['sender_id'] = $row['sender_id'];
						$data['requests'][$row['id']]['receiver_id'] = $row['receiver_id'];
						$_SESSION['requests'] = $data['requests'];
		        	}
			       	
			    }

	        }
	    }
	}

	// fetch messages
	
	// $data = explode(',',$output);
echo json_encode($data);
exit;
}