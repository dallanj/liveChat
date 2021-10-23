<?php
include('head.php');

if($_SESSION['loggedin'] == 1 && $_SESSION['first_login'] == 0) {
	header("Location: dashboard.php");
} elseif($_SESSION['loggedin'] == 1 && $_SESSION['first_login'] == 1 && $_SESSION['verify'] == 1) {
	header("Location: welcome.php");
}

print_r($_SESSION['security']);

$selector = $_GET['selector'];
$validator = $_GET['validator'];

if(!empty($selector) && !empty($validator)) {
    if(ctype_xdigit($selector) !== false && ctype_xdigit($validator) !== false) {
    	header("Location: classes/verify_email.php?selector=" . $selector . "&validator=" . $validator);
    }
}
?>

	<body>

		<div class="container">

			<div class="panel">

				<?php include('alerts.php'); ?>

				<?php
				if(empty($_SESSION['token'])) {
				    $_SESSION['error'] = "Could not validate your request";
				    echo '<div class="alert error">'; 
				    	echo '<span class="closeAlert" onclick="this.parentElement.style=`display:none`">&times;</span>';
				        echo $_SESSION['error'];
				    echo '</div>';       
				    unset($_SESSION['error']);                 
				} else {
				?>

					<div class="login-form">
						<form action="classes/verify_email.php" method="post">
							<input type="text" class="input" name="code" placeholder="Verification code">

							<?php if($_SESSION['loggedin'] != 2) { ?>
								<div class="registerChecks">
									<div>
										<label class="main">
									      <input type="checkbox" name="2fa">
									      Turn on two factor authorization by email
									    </label>
									</div>
								</div>
							<?php } ?>

							<input type="submit" name="submit" class="submit" value="Verify email">

							<input type="hidden" name="recaptcha_response" id="recaptchaResponse">
							

						</form>
					</div>
				<?php } ?>

				<a href="index.php"><button class="submit grey">Back to login</button></a>

				<div class="reCAPTCHA">
					This page is protected by Google reCAPTCHA to ensure you're not a bot. 
				</div>

			</div>

		</div>

	</body>
</html>