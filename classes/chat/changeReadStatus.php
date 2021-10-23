<?php
include('../../inc/db.php');

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $uid = mysqli_real_escape_string($conn,$_POST['uid']); 
    $chat_uid = mysqli_real_escape_string($conn,$_POST['chat_uid']); 
    // update unread message to read
    $sql = "UPDATE messages SET status=1 WHERE receiver_id=? AND sender_id=?";
    $stmt = mysqli_stmt_init($conn);

    if(!mysqli_stmt_prepare($stmt, $sql)) {
        $_SESSION['error'] = 'There was an error, try again later';
    } else {
        mysqli_stmt_bind_param($stmt, "ss", $uid, $chat_uid);
        mysqli_stmt_execute($stmt);
    }
}