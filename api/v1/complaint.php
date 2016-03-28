<?php
/*
	API's
	3.6: Registering complaint
	Method : POST 
	Url : /complaints/<complaint-level>
	POST parameters : 
		1. Individual Complaints
		Url : /complaint/individual
		POST parameters:
			complaint = {
				userId : 32 
				complaint_title : 'Tube not working'
				complaint_description : ''
				concerned_person : 
			}
		2. Hostel Complaints 
		Url : /complaint/hostel
		POST Parameters:
			complaint={
     	   		userId : 32
        		complaint_title : 'Bad mess food'
        		complaint_description : ''
        		concerned_person : 
    		}
    	3. Institute Complaints
		Url : /complaint/institute
		POST Parameters :
    		complaint={
        		userId : 64
		        complaint_title : 'Remove lanban'
		        complaint_description : ''
		        concerned_person :
		    }
	Response : Server responds with success/failure and if success a unique id for the complaint 
	(which can be used to reference/access it later). 
	Example :
    {
        success : 'true'
		complaint_id : 216
	}
*/
	if(isset($_REQUEST['request'])){
		$request = $_REQUEST['request'];
		$data = explode('/', rtrim($request, '/'));
		$errorResponse = json_encode(array("message"=>"invalidRequest"));
		if(count($data)==1){
			$method = $_SERVER['REQUEST_METHOD'];
			switch ($method) {
  				case 'POST':
    				$paramsArray = (array)json_decode(file_get_contents("php://input"));
					if(!isset($paramsArray["userId"]) || !isset($paramsArray["complaint_title"]) || !isset($paramsArray["concerned_person"]) ){
						echo $errorResponse;		
					}else{
						echo registerComplaint($paramsArray, $data[0]);
					}   	
					break;
  				case 'GET':
    				$status = "unresolved";
    				getComplaintList($status);
    				break;
  				default:
				    echo $errorResponse;
    				break;
			}
		}
		else if(count($data) == 2){
			$paramsArray = (array)json_decode(file_get_contents("php://input"));
			if(preg_match('/?status=/',$data[1])){
				$tempData = $data = explode('=', $data[1]);
				$status = $tempData[1];
				getComplaintList($paramsArray,$status);
			}else if(is_int($data[1])){
				getInfoComplaint($data[0], $data[1]);
			}else{
				echo $errorResponse;
			}
		}else if(count($data) == 3){
			switch($data[2]){
				case "upvote":

					break;
				case "downvote":
					
					break;
			}
		}else{
			echo $errorResponse;
		}
	}else{
		echo json_encode(array("message"=>"invalidRequest"));
	}

	function registerComplaint($paramsArray, $compType){
		switch($compType){
			case "individual":
				break;
			case "hostel":
				break;
			case "institute":
				break;
			default:
				break;
		}
	}

	function getComplaintList($status){

	}

	function getInfoComplaint($level, $id){

	}
?>