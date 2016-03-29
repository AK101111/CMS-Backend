<?php
	include '../connect_db.php';
	$msg='';
	if(!empty($_GET['code']) && isset($_GET['code'])){
		$code = mysqli_real_escape_string($connection,$_GET['code']);
		$userQuery = "SELECT userId FROM userVerification WHERE activationCode='$code'";
		$result = $connection->query($userQuery);
		$userId = mysqli_fetch_assoc($result)["userId"];
		if($result->num_rows > 0){
			$isActivated = 0;
			$countQuery =  "SELECT * FROM users WHERE userId='$userId' AND isActivated='$isActivated'";
			$count = $connection->query($countQuery);
			if($count->num_rows == 1){
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




