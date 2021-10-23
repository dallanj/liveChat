<?php
include('head.php');

if($_SESSION['loggedin'] == 1 && $_SESSION['first_login'] == 0) {
	header("Location: dashboard.php");
} elseif($_SESSION['loggedin'] == 1 && $_SESSION['first_login'] == 1 && $_SESSION['verify'] == 1) {
	header("Location: welcome.php");
}

$selector = $_GET['selector'];
$validator = $_GET['validator'];
?>

	<body>

		<div class="container">

			<div class="panel">

				<?php include('alerts.php'); ?>

				<?php
				if(empty($selector) || empty($validator)) {
		            $_SESSION['error'] = "Could not validate your request";
		            echo '<div class="alert error">'; 
				    	echo '<span class="closeAlert" onclick="this.parentElement.style=`display:none`">&times;</span>';
				        echo $_SESSION['error'];
				    echo '</div>';       
				    unset($_SESSION['error']);                 
		        } else {
		            if(ctype_xdigit($selector) !== false && ctype_xdigit($validator) !== false) {
		        ?>
					<div class="login-form">
						<form action="classes/reset_password.php" method="post">
							<input hidden name="selector" value="<?php echo $selector; ?>">
                    		<input hidden name="validator" value="<?php echo $validator; ?>">
							<input type="password" class="input" id="psw" name="password" placeholder="New password">

							<div id="conditions" class="alert info">Make sure your password is atleast <b>8 characters</b> long, contains a <b>lowercase</b> and an <b>uppercase</b> letter, and a <b>number</b></div>

							<input type="password" class="input" id="confirm" name="confirm" placeholder="Confirm password">

							<div id="match" class="alert info">
		                	Both passwords must <b>match</b>
		            		</div>

							<input type="submit" name="submit" class="submit" value="Change password">

							<input type="hidden" name="recaptcha_response" id="recaptchaResponse">

						</form>
					</div>
				<?php
					}
				}
				?>

				<a href="index.php"><button class="submit grey">Back to login</button></a>

				<div class="reCAPTCHA">
					This page is protected by Google reCAPTCHA to ensure you're not a bot. 
				</div>

			</div>

		</div>

	</body>
</html>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="inc/scripts.js"></script>