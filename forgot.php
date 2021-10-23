<?php
include('head.php');

if($_SESSION['loggedin'] == 1 && $_SESSION['first_login'] == 0) {
	header("Location: dashboard.php");
} elseif($_SESSION['loggedin'] == 1 && $_SESSION['first_login'] == 1 && $_SESSION['verify'] == 1) {
	header("Location: welcome.php");
}
echo $_SESSION['loggedin'];
?>

	<body>

		<div class="container">

			<div class="panel">

				<?php include('alerts.php'); ?>

				<div class="login-form">
					<form action="classes/password_reset_request.php" method="post">
						<input type="text" class="input" name="username" placeholder="Username or email">

						<input type="submit" name="submit" class="submit" value="Request new password">

						<input type="hidden" name="recaptcha_response" id="recaptchaResponse">

					</form>
				</div>

				<a href="index.php"><button class="submit grey">Back to login</button></a>

				<div class="reCAPTCHA">
					This page is protected by Google reCAPTCHA to ensure you're not a bot. 
				</div>

			</div>

		</div>

	</body>
</html>