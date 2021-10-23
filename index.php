<?php
include('head.php');

if($_SESSION['loggedin'] == 1 && $_SESSION['first_login'] == 0) {
	header("Location: dashboard.php");
} elseif($_SESSION['loggedin'] == 1 && $_SESSION['first_login'] == 1 && $_SESSION['verify'] == 1) {
	header("Location: welcome.php");
}
?>

	<body>

		<div class="container">

			<div class="panel">

				<?php include('alerts.php'); ?>

				<div class="login-form">
					<form action="classes/checklogin.php" method="post">
						<input type="text" class="input" name="username" placeholder="Username or email" value="<?php echo $_GET['user']; ?>">
						<input type="password" class="input" name="password" placeholder="Password">
						<input type="submit" name="submit" class="submit" value="Login">
						<input type="hidden" name="recaptcha_response" id="recaptchaResponse">
						
						<div class="rememberForgot">
							<div>
								<label class="main">
							      <input type="checkbox" name="remember">
							      Remember me
							    </label>
							</div>
							<div>
								<a href="forgot.php">Forgot password?</a>
							</div>
						</div>

					</form>
				</div>

				<a href="register.php"><button class="submit grey">Sign up</button></a>

						<div class="reCAPTCHA">
							This page is protected by Google reCAPTCHA to ensure you're not a bot. 
						</div>

			</div>

		</div>

	</body>
</html>