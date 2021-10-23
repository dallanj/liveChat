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
					<form action="classes/register.php" method="post">
						<input type="text" class="input" id="username" name="username" placeholder="Username" value="<?php echo $_GET['user']; ?>">

						<!-- Alert tool tip -->
			                <div id="userConditions" class="bubble right">
			                    <p id="userConditionsColor" class="info">Username must be atleast <b>3 characters long</b> and may only contain <b>letters</b> and <b>numbers</b></p>
			                </div>

						<input type="text" class="input" name="email" placeholder="Email address" value="<?php echo $_GET['mail']; ?>">
						<input type="password" class="input" id="psw" name="password" placeholder="Password">

						<!-- Alert tool tip -->
							<div id="conditions" class="bubble left">
			                    <p id="conditionsColor" class="info">Make sure your password is atleast <b>8 characters</b> long, contains a <b>lowercase</b> and an <b>uppercase</b> letter, and a <b>number</b></p>
			                </div>

						<input type="password" class="input" id="confirm" name="confirm" placeholder="Confirm password">

						<!-- Alert tool tip -->
			                <div id="match" class="bubble right">
			                    <p id="matchColor" class="info">Confirm that both of your passwords <b>match</b></p>
			                </div>

						<div class="registerChecks">
							<div>
								<label class="main">
							      <input type="checkbox" name="notify">
							      Email about news and updates
							    </label>
							</div>
							<div>
								<label class="main">
							      <input type="checkbox" name="agree">
							      Agree to terms and conditions
							    </label>
							</div>
						</div>

						<input type="submit" name="submit" class="submit" value="Sign up">
						
						<input type="hidden" name="recaptcha_response" id="recaptchaResponse">

					</form>
				</div>

				<a href="index.php"><button class="submit grey">Login</button></a>

				<div class="reCAPTCHA">
					This page is protected by Google reCAPTCHA to ensure you're not a bot. 
				</div>

			</div>

		</div>

	</body>
</html>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="inc/scripts.js"></script>