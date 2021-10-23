<?php
include('../../inc/db.php');

if($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $receiver = mysqli_real_escape_string($conn,$_POST['friendRequest']);   

    if(empty($receiver)) {
        $alert['message'] = 'Error! Please enter a username';
        $alert['type'] = 'error';

    } elseif($receiver == $_SESSION['USERNAME']) {
        $alert['message'] = 'Error! You cant send yourself a friend request';
        $alert['type'] = 'error';
    } else {
        $receiver = mysqli_real_escape_string($conn,$receiver);     

        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql)) {
            $alert['message'] = 'The server is having trouble connecting, try again later';
            $alert['type'] = 'error';
        } else {
            mysqli_stmt_bind_param($stmt, "s", $receiver);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $count = mysqli_num_rows($result);

            if($count == 1) { 

                while($row = mysqli_fetch_assoc($result)) {
                    $receiver_id = $row['id'];
                    if(in_array($receiver_id, $_SESSION['friends'])) {
                        $alert['message'] = 'Error! '.$receiver.' is already your friend';
                        $alert['type'] = 'error';
                    } else {
                        $sql1 = "SELECT * FROM friend_request WHERE sender_id = ? AND receiver_id = ?";
                        $stmt1 = mysqli_stmt_init($conn);
                        if(!mysqli_stmt_prepare($stmt1, $sql1)) {
                            $alert['message'] = 'Error! The server is having trouble connecting, try again later';
                            $alert['type'] = 'error';
                        } else {
                            mysqli_stmt_bind_param($stmt1, "ss", $_SESSION['UID'], $receiver_id);
                            mysqli_stmt_execute($stmt1);

                            $result1 = mysqli_stmt_get_result($stmt1);
                            $count1 = mysqli_num_rows($result1);

                            if($count1 >= 1) { 
                                $alert['message'] = 'You have already sent '.$receiver.' a friend request';
                                $alert['type'] = 'info';
                            } else {
                                $sql = "INSERT INTO friend_request (sender_id, receiver_id) VALUES (?, ?);";
                                $stmt = mysqli_stmt_init($conn);
                                if(!mysqli_stmt_prepare($stmt, $sql)) {
                                    $alert['message'] = 'Error! The server is having trouble connecting, try again later';
                                    $alert['type'] = 'error';
                                } else {
                                    mysqli_stmt_bind_param($stmt, "ss", $_SESSION['UID'], $receiver_id);
                                    mysqli_stmt_execute($stmt);
                                    $alert['message'] = 'Success! You sent '.$receiver.' a friend request';
                                    $alert['type'] = 'success';
                                }
                            }
                        }
                    }
                }
            } else {
                $alert['message'] = 'Error! User does not exist';
                $alert['type'] = 'error';
            }
            
        }   
    } 
    echo json_encode($alert);
}