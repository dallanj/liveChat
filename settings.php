<div class="top_of_chat padding">
	<div id="accountSettings">
		<div class="title padding5">Account settings for <?php echo $_SESSION['USERNAME']; ?>
			
		</div>
	</div>
</div>


<div class="accountContainer">

	<div>
		<form action="./classes/update_email.php" method="POST">
			<p class="formTitle">Update email</p>

			<p class="formSubtitle">Email</p>
			<input type="text" class="input" name="email" placeholder="Email address" value="<?php echo $_SESSION['EMAIL']; ?>">

			<p class="formSubtitle">Password</p>
			<input type="password" class="input" name="password" placeholder="Password">
			
			<input type="submit" name="submit" class="saveButton" value="Update email">

		</form>
	</div>

	<div>
		<form action="./classes/update_email.php" method="POST">
			<p class="formTitle">Profile photo</p>
			<img width="150" src="./uploads/no_pic.jpg">
		
			<input type="submit" class="changePicButton" value="Upload an Image">

			<input type="hidden" name="recaptcha_response" id="recaptchaResponse">
		</form>
	</div>

</div>

<div class="accountContainer">

	<div>
		<form action="./classes/update_password.php" method="POST">
			<p class="formTitle">Update password</p>

			<p class="formSubtitle">Old password</p>
			<input type="password" class="input" name="password" placeholder="Current password">

			<p class="formSubtitle">New password</p>
			<input type="password" class="input" name="newPassword" placeholder="New password">

			<p class="formSubtitle">Confirm password</p>
			<input type="password" class="input" name="confirmPassword" placeholder="Confirm password">
			
			<input type="submit" name="submit" class="saveButton" value="Update password">

		</form>
	</div>

</div>

<div class="accountContainer">

	<div>
		<form action="classes/update_preferences.php" method="post">
			<p class="formTitle">Update preferences</p>

					<p class="formSubtitle">
						
				      	<input <?php if($_SESSION['notify'] === 1) echo 'checked'; ?> type="checkbox" name="notify">
				      	Email about news and updates
				    </p>
			
					<p class="formSubtitle">
						<input <?php if($_SESSION['security'] === 1) echo 'checked'; ?> type="checkbox" name="2fa">
						Turn on two factor authorization by email
					</p>

			<p>
			<input type="password" class="input" name="password" placeholder="Password">
			<input type="submit" name="submit" class="saveButton" value="Update preferences">
			<input type="hidden" name="recaptcha_response" id="recaptchaResponse">
		</form>
	</div>

</div>
		