<?php
include('header.php');


if($_SESSION['loggedin'] == 0 && $_SESSION['first_login'] == 0 || $_SESSION['loggedin'] == 2) {
	header("Location: index.php");
}
if($_SESSION['loggedin'] == 1 && $_SESSION['first_login'] == 1) {
  	header("Location: welcome.php");
}
?>


<html>
    <body>
    	<div class="contentContainer">
			<?php
	    		include './classes/chat/scripts.php';
	    		include './alerts.php';
	    	?>
			<div class="navContainer padding5">

	    		<!-- Your profile status and name -->
	    		<div id="myProfile" class="padding5"></div>

	    		
		        <!-- Add friend by username -->
	    		<div class="addFriendInput">
		            <form id="friendRequestForm" method="post">
		                <input type="text" class="search_friend"  name="friendRequest" id="friendRequest" placeholder="Add friend by username">
		            </form>
		            <button class="submit_friend" id="submitFriendRequest" name="submitFriendRequest"><i class="fa fa-search"></i></button>
		        </div>

	    		<!-- Incoming friend requests -->
	    		<div id="incomingRequests"></div>

	    		<!-- List of friends -->
	    		<div class="friendList" id="friendList"></div>

	    	</div>

	    	
	    		<div class="chatContainer" id="chatSessionContainer">
				</div>
	    	
	    		<div class="settingsContainer">
    				<?php include './settings.php'; ?>
    			</div>

	    	</div>
	   
	</body>
</html>