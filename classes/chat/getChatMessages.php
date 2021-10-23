<?php
include('../../inc/db.php');

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // $data = array();
    
    $sql = "SELECT * FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)";
    $stmt = mysqli_stmt_init($conn);
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $_POST['sender_id'], $_POST['receiver_id'], $_POST['receiver_id'], $_POST['sender_id']);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    while($row = mysqli_fetch_assoc($result)) {
        $sql1 = "SELECT * FROM users WHERE id=?";
        $stmt1 = mysqli_stmt_init($conn);

        if(!mysqli_stmt_prepare($stmt1, $sql1)) {
            $_SESSION['error'] = 'There was an error, try again later';
        } else {
            mysqli_stmt_bind_param($stmt1, "s", $row['sender_id']);
            mysqli_stmt_execute($stmt1);

            $result1 = mysqli_stmt_get_result($stmt1);
            $row_count1 = mysqli_num_rows($result1);
            while($row1 = mysqli_fetch_assoc($result1)) {
                $username = $row1['username'];
                $userUid = $row1['id'];
                
            }
        }
                $data['messages'][$row['id']]['id'] = $row['id'];
                $data['messages'][$row['id']]['sender_id'] = $row['sender_id'];
                $data['messages'][$row['id']]['receiver_id'] = $row['receiver_id'];
                $data['messages'][$row['id']]['message'] = $row['message'];
                $data['messages'][$row['id']]['sessionUid'] = $_SESSION['UID'];
                $data['messages'][$row['id']]['messageUid'] = $userUid;
                $data['messages'][$row['id']]['sessionUser'] = $_SESSION['USERNAME'];
                $data['messages'][$row['id']]['messageUser'] = $username;
    }
    echo json_encode($data);
}