<?php
include('head.php');

if($_SESSION['loggedin'] == 0 && $_SESSION['first_login'] == 0 || $_SESSION['loggedin'] == 2) {
	header("Location: index.php");
}
if($_SESSION['loggedin'] == 1 && $_SESSION['first_login'] == 0) {
  header("Location: dashboard.php");
}
?>

	<body>

		<div class="container">

			<div class="panel">

				<?php include('alerts.php'); ?>

				<div class="login-form">
					Welcome, <?php echo $_SESSION['USERNAME']; ?>

					<p>Thank you for choosing to use liveChat. This project was made by Dallan. Click <a href="https://dallan.ca">here</a> to view my portfolio for other projects</p>
					<form action="classes/welcome_msg.php" method="post">
						<input type="submit" name="submit" class="submit" value="Okay!">
						<input type="hidden" name="recaptcha_response" id="recaptchaResponse">
					</form>
				</div>

			</div>

		</div>

	</body>
</html>