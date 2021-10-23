<?php
// Start of Remember Me Script
if(isset($_SESSION['remember']) || isset($remember)) {
    $selector = base64_encode(random_bytes(9));
    $authenticator = random_bytes(33);
    $hashedToken = hash('sha256', $authenticator);
    $expires = date('Y-m-d\TH:i:s', time() + 864000);

    setcookie(
        'remember',
        $selector.':'.base64_encode($authenticator),
        time() + 864000,
        '/'
    );

    $sql = "INSERT INTO auth_tokens (userid, selector, token, expires) VALUES (?, ?, ?, ?);";
    $stmt = mysqli_stmt_init($conn);

    if(!mysqli_stmt_prepare($stmt, $sql)) {
      $_SESSION['error'] = 'There was an error, try again later';
          header("Location: ../index.php");
    } else {
      mysqli_stmt_bind_param($stmt, "ssss", $_SESSION['UID'], $selector, $hashedToken, $expires);
      mysqli_stmt_execute($stmt);
      unset($_SESSION['remember']);
    }
}
// End of Remember Me Script