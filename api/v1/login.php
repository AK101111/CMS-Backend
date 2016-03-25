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
	if(isset($_REQUEST['request'])){
		$request = $_REQUEST['request'];
		$data = explode('/', rtrim($request, '/'));
		$errorResponse = json_encode(array("message"=>"invalidRequest"));
		if($data[0] == "login"){
			$paramsArray = (array)json_decode(file_get_contents("php://input"));
			if(!isset($paramsArray["userName"]) || !isset($paramsArray["password"])){
				echo $errorResponse;		
			}else{
				echo login($paramsArray);
			}
		}else if($data[0] == "logout"){
			echo logout();
		}else{
			echo $errorResponse;
		}
	}else{
		echo json_encode(array("message"=>"invalidRequest"));
	}

	function userLogin($paramsArray){
		//TODO -> add cookies. 
		include("../connect_db.php");
		$userName = $paramsArray["userName"];
		$password = $paramsArray["password"];
		$verifyQuery = "SELECT password FROM users WHERE userName = '$userName'";
		$encryptedPass = $connection->query($verifyQuery);
		// Hashing the password with its hash as the salt returns the same hash 
		if ( hash_equals($encryptedPass, crypt($password, $encryptedPass)) ) {
  			$response = array('success' => "true");
		}else{
			$response = array('success' => "false");
		}
		return json_encode($response);
	}

	function userLogout(){
		// TODO -> invalidate cookies.
	}
?>