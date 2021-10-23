<?php
include('../../inc/db.php');

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chat_userid = mysqli_real_escape_string($conn,$_POST['chat_userid']);
    $uid = $_SESSION['UID'];

    $sql = "SELECT * FROM users WHERE id=?";
    $stmt = mysqli_stmt_init($conn);

    if(!mysqli_stmt_prepare($stmt, $sql)) {
        $_SESSION['error'] = 'There was an error, try again later';
    } else {
        mysqli_stmt_bind_param($stmt, "s", $chat_userid);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $row_count = mysqli_num_rows($result);
        while($row = mysqli_fetch_assoc($result)) {

            // Check if user exist in their friends list
            $friends = explode(',',$row['friends']);

            // foreach($friends as $friend) {
                if(!in_array($uid, $friends)) {
                    $data = null;
                } else {
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

                    $data['profile']['id'] = $row['id'];
                    $data['profile']['username'] = $row['username'];
                    $data['profile']['email'] = $row['email'];
                    $data['profile']['last_active'] = $row['last_active'];
                    $data['profile']['online_status'] = $row['online_status'];
                    $data['profile']['status_color'] = $status_color;
                    $data['profile']['active_now'] = $active_now;
                    
                }
            // }


            
        }
    }
    echo json_encode($data);
}