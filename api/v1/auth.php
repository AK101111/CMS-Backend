<?php
/*
	API's
	3.1 Logging In
		Method : POST
		Url : auth/login
		POST parameters : user → should be a json object, having the username and password of the user. Example:
    	user={
        	username : 'ee1130431'
        	password : 'winteriscomings6'
		}
		Response : Sever responds with success/failure status. Example: {
        	success : 'true'
    	}

    3.2 Logout 
    	Method : GET
		Url : auth/logout

*/

	// handles login and logout requests.
	if(isset($_REQUEST['request'])){
		$request = $_REQUEST['request'];
		$data = explode('/', rtrim($request, '/'));
		session_start();
		//setcookie("SESSION_ID",session_id(),time()+3600,"/api/");
		$errorResponse = json_encode(array("message"=>"invalidRequest"));
		if($data[0] == "login"){
			$paramsArray = (array)json_decode(file_get_contents("php://input"));
			if(!isset($paramsArray["userName"]) || !isset($paramsArray["password"])){
				echo $errorResponse;		
			}else{
				echo userLogin($paramsArray);
			}
		}else if($data[0] == "logout"){
			echo userLogout();
		}else{
			echo $errorResponse;
		}
	}else{
		echo json_encode(array("message"=>"invalidRequest"));
	}

	// function which carries out actual login
	function userLogin($paramsArray){
		//TODO -> add cookies. 
		include("../connect_db.php");
		$userName = $paramsArray["userName"];
		$password = $paramsArray["password"];
		$verifyQueryString = "SELECT * FROM users WHERE userName = '$userName'";
		$verifyQuery = $connection->query($verifyQueryString);
		$resultRow = mysqli_fetch_assoc($verifyQuery);
		$savedPassword = $resultRow["password"];
		if($verifyQuery->num_rows > 0){
			if($savedPassword == $password){
				$response = array(	
									'message' => 'success',
									'userData'=>array(
												'userId' => $resultRow['userId'],
												'firstName' => $resultRow['firstName'],
												'lastName' => $resultRow['lastName'],
												'userName' => $resultRow['userName'],
												'hostel' => $resultRow['hostel'],
												'userType' => $resultRow['userType']
											)
								);
				if($resultRow['userType'] == "staff"){
					$staffId = $resultRow['userId'];
					$response['userData']['staffScope'] = mysqli_fetch_assoc($connection->query("SELECT * FROM staffScopes WHERE staffId = '$staffId'"))['staffScope'];
				}
			}else{
				$response = array('message' => 'incorrectPassword');	
			}

		}else{
			$response = array('message' => 'invalidUserName');
		}
		return json_encode($response);
	}

	// function for logout
	function userLogout(){
		// TODO -> invalidate cookies.
	}
?>