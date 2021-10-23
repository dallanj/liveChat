<?php
echo '<div id="alerts">';   
    if(isset($_SESSION['error'])) {
        echo '<div class="alert error">'; 
        	echo '<span class="closeAlert">&times;</span>';
            echo $_SESSION['error'];
        echo '</div>';       
        unset($_SESSION['error']);
    }
    if(isset($_SESSION['success'])) {
         echo '<div class="alert success">';
        	echo '<span class="closeAlert">&times;</span>';
            echo $_SESSION['success'];
        echo '</div>';       
        unset($_SESSION['success']);
    }
    if(isset($_SESSION['info'])) {
        echo '<div class="alert info">';
        	echo '<span class="closeAlert">&times;</span>';
            echo $_SESSION['info'];
        echo '</div>';       
        unset($_SESSION['info']);
    }
echo '</div>';