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
					if(!isset($paramsArray["userId"]) || 
						!isset($paramsArray["title"]) || 
						!isset($paramsArray["description"])){
						echo $errorResponse;		
					}else{
						echo registerComplaint($paramsArray, $data[0]);
					}   	
					break;
  				case 'GET':
  					$paramsArray = (array)json_decode(file_get_contents("php://input"));
    				$status = "unresolved";
    				getComplaintList($data[0],$paramsArray["scope"],$status);
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
				getComplaintList($data[0],$paramsArray["scope"],$status);
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
		include('../connect_db.php');
		$title = $paramsArray["title"];
		$description = $paramsArray["description"];
		$type = $compType;
		$scope = $paramsArray["scope"];
		$creatorId = $paramsArray["userId"];
		$createdTime = date("d-m-Y H:i:s");
		$photoAvailable = 0;
		if(isset($paramsArray["image"])){
			$photoAvailable = 1;
		}
		$tableName = "complaints";
		$addIssue = "INSERT INTO $tableName(title,description,scope,type,createdTime,creatorId,photoAvailable)".
						"VALUES('$title','$description','$scope','$type','$createdTime','$creatorId','$photoAvailable')";
		$addIssueResult = $connection->query($addIssue);
		$result = array();
		$result["message"] = "submitSuccess"; 
		$complaintId = $connection->query("SELECT id from $tableName WHERE createdTime = '$createdTime'")->fetch_row()[0];
		
		if(isset($paramsArray["image"])){
			$base64Image = $paramsArray["image"];
			$imageData = base64_decode($base64Image);
			$source = imagecreatefromstring($imageData);
			$imageSave = imagejpeg($source,"../../images/".$complaintId.'.jpg',100);
		}
		imagedestroy($source);
	}

	function getComplaintList($level,$scope,$status){
		include('../connect_db.php');
		$query = "SELECT * from complaints WHERE type = '$level' AND scope = '$scope'";
		if($status != "all")
			$query .= " AND status = '$status'";
		$complaintListQuery = $connection->query($query);
		$complaintList = array('complaints' => array());
		for($i = 0; $i < $complaintListQuery->num_rows; $i++){
			$row = mysqli_fetch_assoc($complaintListQuery);
			$complaint = array(
								'id' => $row['id'],
								'title' => $row['title'],
								'description' => $row['description'],
								'scope' => $row['scope'],
								'type' => $row['type'],
								'createdTime' => $row['createdTime'],
								'creatorId' => $row['creatorId'],
								'photoAvailable' => $row['photoAvailable'],
							);
			$complaintList['complaints'][$i] = $complaint;
		}
		return $complaintList;
	}

	function getInfoComplaint($level, $id){
		include('../connect_db.php');
		$query = "SELECT * from complaints WHERE type = '$level' AND id = '$id'";
		$complaintListQuery = $connection->query($query);
		$complaint = array();
		$row = mysqli_fetch_assoc($complaintListQuery);
		$complaint['complaintDetails'] = array(
							'id' => $row['id'],
							'title' => $row['title'],
							'description' => $row['description'],
							'scope' => $row['scope'],
							'type' => $row['type'],
							'createdTime' => $row['createdTime'],
							'creatorId' => $row['creatorId'],
							'photoAvailable' => $row['photoAvailable'],
						);
		return $complaint;
	}
?>