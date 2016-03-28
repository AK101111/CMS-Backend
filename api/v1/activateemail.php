<?php
	include 'connect_db.php';
	$msg='';
	if(!empty($_GET['code']) && isset($_GET['code'])){
		$code = mysqli_real_escape_string($connection,$_GET['code']);
		$userQuery = "SELECT userId FROM userVerification WHERE activationCode='$code'";
		$connection->query($userQuery);
		if(mysqli_num_rows($userQuery) > 0){
			$countQuery =  "SELECT * FROM users WHERE userId='$userId' AND isActivated=0";
			$count = $connection->query($countQuery);
			if(mysqli_num_rows($count) == 1){
				$updateQuery = "UPDATE users SET isActivated='1' WHERE userId='$userId'";
				$connection->query($updateQuery);
				$msg="Account is activated"; 
			}else{
				$msg ="user already registered, and activated.";
			}
		}else{
			$msg ="Incorrect code.";
		}
	}
	echo $msg; 
?>





