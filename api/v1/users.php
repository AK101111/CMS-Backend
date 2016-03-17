<?php
	$request = $_REQUEST['request'];
	$data = explode('/', rtrim($request, '/'));
	header('Content-Type:application/json; charset=utf-8');
	$errorResponse = json_encode(array("message"=>"invalidRequest"));
	if($data[0] == "register"){
		echo "you can register very soon";
	}else if($data[0] == "list"){
		if(!isset($data[1])){
			echo processTypeRequest('');
		}else if(sizeof($data) > 2){
			echo $errorResponse;
		}else if(isset($data[1]) && ($data[1]=="student" || $data[1]=="faculty" || $data[1]=="staff")){
			echo processTypeRequest($data[1]);
		}else{
			 echo $errorResponse;
		}
	}else if($data[0] == "user"){
		if(isset($data[1]) && ctype_digit($data[1])){
			echo getUserDetails($data[1]);
		}else
			echo $errorResponse;
	}else{
		echo $errorResponse;
	}

	function processTypeRequest($userType){
		include("../connect_db.php");
		$query = "SELECT userId FROM users";
		if($userType != '')
			$query .= " WHERE userType='$userType'";
		$users = $connection->query($query);
		$len = $users->num_rows;
		$userData = array();
		if($len != 0){
			$userData["users"] = array();
			$userData["message"] = "usersFound";
			for($i = 0; $i < $len; $i++){
				$row = $users->fetch_row();
				$userData["users"][$i] = (int)$row[0];
			}
		}else
			$userData["message"] = "noUsersFound";
		return json_encode($userData);
	}

	function getUserDetails($userId){
		include("../connect_db.php");
		$query = "SELECT * FROM users WHERE userId='$userId'";
		$users = $connection->query($query);
		$len = $users->num_rows;
		$userData = array();
		if($len != 0){
			$userData["user"] = array();
			$userData["message"] = "userFound";
			$row = mysqli_fetch_assoc($users);
			$userData["user"]["userId"] = (int)$row["userId"];
			$userData["user"]["firstName"] = $row["firstName"];
			$userData["user"]["lastName"] = $row["lastName"];
			$userData["user"]["userName"] = $row["userName"];
			$userData["user"]["userType"] = $row["userType"];
			$userData["user"]["hostel"] = (int)$row["hostel"];
			$userData["user"]["isActivated"] = (int)$row["isActivated"];
		}else
			$userData["message"] = "userNotFound";
		return json_encode($userData);
	}
?>
