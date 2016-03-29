<?php
	header('Content-Type:application/json; charset=utf-8');
	if(isset($_REQUEST['request'])){
		$request = $_REQUEST['request'];
		$data = explode('/', rtrim($request, '/'));
		$errorResponse = json_encode(array("message"=>"invalidRequest"));
		// handling requests of type /users
		if($data[0] == "register"){
			// registering a user initial checks
			$paramsArray = (array)json_decode(file_get_contents("php://input"));
			if(!isset($paramsArray["firstName"]) || !isset($paramsArray["lastName"]) || !isset($paramsArray["userName"]) 
				|| !isset($paramsArray["password"]) || !isset($paramsArray["hostel"]) || !isset($paramsArray["userType"])){
				echo $errorResponse;		
			}else{
				echo registerUser($paramsArray);
			}
		}else if($data[0] == "list"){
			// get user lists, initial checks
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
			// get user information, initial checks
			if(!isset($data[1]) || !ctype_digit($data[1]) || sizeof($data) > 2){
				echo $errorResponse;
			}else
				echo getUserDetails($data[1]);
		}else{
			echo $errorResponse;
		}
	}else{
		echo json_encode(array("message"=>"invalidRequest"));
	}

	// actual function which fetches users list from db
	function processTypeRequest($userType){
		include("../connect_db.php");
		$query = "SELECT * FROM users";
		if($userType != '')
			$query .= " WHERE userType='$userType'";
		$users = $connection->query($query);
		$len = $users->num_rows;
		$userData = array();
		if($len != 0){
			$userData["users"] = array();
			$userData["message"] = "usersFound";
			for($i = 0; $i < $len; $i++){
				$row = mysqli_fetch_assoc($users);
				if($userType==""){
					$userData["users"][$i] = array("userId"=>(int)$row["userId"],"userType"=>$row["userType"]);
				}else
					$userData["users"][$i] = (int)$row["userId"];
			}
		}else
			$userData["message"] = "noUsersFound";
		return json_encode($userData);
	}

	// actual function which fetches user info from db
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
	
	// reistering user main function
	function registerUser($paramsArray){
		include("../connect_db.php");
		$firstName = $paramsArray["firstName"];
		$lastName = $paramsArray["lastName"];
		$userName = $paramsArray["userName"];
		$password = $paramsArray["password"];
		//$password = hashPassword($password);
		$hostel = $paramsArray["hostel"];
		$userType = $paramsArray["userType"];
		$checkQuery = "select userId from users where userName = '$userName'";
		$registerQuery = "INSERT INTO users(firstName,lastName,userName,password,hostel,userType) VALUES('$firstName','$lastName','$userName','$password','$hostel','$userType')";
		$userCheck = $connection->query($checkQuery);
		if($userCheck->num_rows > 0){
			$error = array('message' => "userAlreadyRegistered");
			return json_encode($error);
		}else{
			$register = $connection->query($registerQuery);
			$result = array("message"=>"success");
			$result["userId"] = mysqli_fetch_assoc($connection->query($checkQuery))["userId"];
			if($userType == "staff")
				saveStaffScopeId($connection,$result["userId"],$paramsArray["scopeId"]);
			if($userType != "staff"){
				// if not staff, user needs to verify by clicking on link sent on email.
				$to=$userName . "@iitd.ac.in";
				$activation=md5($to.time()); // encrypted email+timestamp
				$userId = $result["userId"];
				$activationstoreQuery = "INSERT INTO userVerification(userId,activationCode) VALUES('$userId','$activation')";
				$connection->query($activationstoreQuery);
				$subject="Email verification";
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				$headers .= 'From: noreply@dmpiitd.esy.es' . "\r\n";
				$body='Hi, <br/> <br/> Please verify your email and get started using your account. <br/> <br/> <a href="'.$base_url.'activation/'.$activation.'">'.$base_url.'activation/'.$activation.'</a>';
				mail($to,$subject,$body,$headers);
				//$msg= "Registration successful, please activate email."; 
			}
			return json_encode($result);
		}
	}
	function saveStaffScopeId($connection,$userId,$scopeId){
		$connection->query("INSERT INTO staffScopes(userId,scopeId) VALUES('$userId','$scopeId')");
	}
	// function to encrypt password to store encrypted pass on disk
	function hashPassword($password){
		$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
		$security = 9;
		// using CRYPT_BLOWFISH
		$salt = sprintf("$2a$%02d$", $security) . $salt;
		$hash = crypt($password, $salt);
		return $hash;
	}
?>
