<?php
include('../../inc/db.php');

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $sender_id = mysqli_real_escape_string($conn,$_POST['sender_id']);

    if($_POST['response'] == 'accept') {      

        $sql = "SELECT * FROM friend_request WHERE sender_id = ? AND receiver_id = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql)) {
            $alert['message'] = 'The server is having trouble connecting, try again later';
            $alert['type'] = 'error';
        } else {
            mysqli_stmt_bind_param($stmt, "ss", $sender_id, $_SESSION['UID']);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $count = mysqli_num_rows($result);

            if($count == 1) { 
            // grab receivers friends list
                $sql = "SELECT * FROM users WHERE id = ?";
                $stmt = mysqli_stmt_init($conn);
                if(!mysqli_stmt_prepare($stmt, $sql)) {
                    $alert['message'] = 'The server is having trouble connecting, try again later';
                    $alert['type'] = 'error';
                } else {
                    mysqli_stmt_bind_param($stmt, "s", $_SESSION['UID']);
                    mysqli_stmt_execute($stmt);

                    $result = mysqli_stmt_get_result($stmt);
                    while($row = mysqli_fetch_assoc($result)) {
                        $receivers_friends = $row['friends'];

                        $receivers_friends = $receivers_friends.','.$sender_id;
                    // add senders id to receivers friends list
                        $sql = "UPDATE users SET friends = ? WHERE id = ?";
                        $stmt = mysqli_stmt_init($conn);

                        if(!mysqli_stmt_prepare($stmt, $sql)) {
                            $alert['message'] = 'The server is having trouble connecting, try again later';
                            $alert['type'] = 'error';       
                        } else {
                            mysqli_stmt_bind_param($stmt, "ss", $receivers_friends, $_SESSION['UID']);
                            mysqli_stmt_execute($stmt);
                        }
                    }
                }
            // grab senders friends list
                $sql = "SELECT * FROM users WHERE id = ?";
                $stmt = mysqli_stmt_init($conn);
                if(!mysqli_stmt_prepare($stmt, $sql)) {
                    $alert['message'] = 'The server is having trouble connecting, try again later';
                    $alert['type'] = 'error';
                } else {
                    mysqli_stmt_bind_param($stmt, "s", $sender_id);
                    mysqli_stmt_execute($stmt);

                    $result = mysqli_stmt_get_result($stmt);
                    while($row = mysqli_fetch_assoc($result)) {
                        $senders_friends = $row['friends'];
                        $senders_username = $row['username'];
                        $senders_friends = $senders_friends.','.$_SESSION['UID'];
                    // add receivers id to senders friends list
                        $sql = "UPDATE users SET friends = ? WHERE id = ?";
                        $stmt = mysqli_stmt_init($conn);

                        if(!mysqli_stmt_prepare($stmt, $sql)) {
                            $alert['message'] = 'The server is having trouble connecting, try again later';
                            $alert['type'] = 'error';
                        } else {
                            mysqli_stmt_bind_param($stmt, "ss", $senders_friends, $sender_id);
                            if(mysqli_stmt_execute($stmt)) {
                                $alert['message'] = 'Success! You are now friends with '.$senders_username;
                                $alert['type'] = 'success';
                            }
                        }
                    }
                }

            // delete friend requests of both users
                $sql = "DELETE FROM friend_request WHERE sender_id = ? AND receiver_id = ?";
                $stmt = mysqli_stmt_init($conn);

                mysqli_stmt_prepare($stmt, $sql);
                mysqli_stmt_bind_param($stmt, "ss", $sender_id, $_SESSION['UID']);
                mysqli_stmt_execute($stmt);

                $sql = "DELETE FROM friend_request WHERE sender_id = ? AND receiver_id = ?";
                $stmt = mysqli_stmt_init($conn);

                mysqli_stmt_prepare($stmt, $sql);
                mysqli_stmt_bind_param($stmt, "ss", $_SESSION['UID'], $sender_id);
                mysqli_stmt_execute($stmt);
            } else {
                $alert['message'] = 'The server is having trouble connecting, try again later';
                $alert['type'] = 'error';
            }
            
        }   
    } elseif($_POST['response'] == 'decline') { 
    // delete friend requests of both users
            $sql = "DELETE FROM friend_request WHERE sender_id = ? AND receiver_id = ?";
            $stmt = mysqli_stmt_init($conn);

            mysqli_stmt_prepare($stmt, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $sender_id, $_SESSION['UID']);
            mysqli_stmt_execute($stmt);

            $sql = "DELETE FROM friend_request WHERE sender_id = ? AND receiver_id = ?";
            $stmt = mysqli_stmt_init($conn);

            mysqli_stmt_prepare($stmt, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $_SESSION['UID'], $sender_id);
            mysqli_stmt_execute($stmt);    
    }
    //unset($_SESSION['requests']);
    echo json_encode($alert);
}
// End of Check Login Script







