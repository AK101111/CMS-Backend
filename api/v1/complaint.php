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
	header('Content-Type:application/json; charset=utf-8');
	if(isset($_REQUEST['request'])){
		$request = $_REQUEST['request'];
		$data = explode('/', rtrim($request, '/'));
		$errorResponse = json_encode(array("message"=>"invalidRequest"));
		if(count($data)==1){
			$method = $_SERVER['REQUEST_METHOD'];
			switch ($method) {
  				case 'POST':
    				$paramsArray = (array)json_decode(file_get_contents("php://input"));
    				//print_r($paramsArray);
					if(!isset($paramsArray["userId"]) || 
						!isset($paramsArray["title"]) || 
						!isset($paramsArray["description"])){
						echo $errorResponse;		
					}else{
						echo registerComplaint($paramsArray, $data[0]);
					}   	
					break;
  				case 'GET':
  					//print_r(expression)
  					//$params = explode('?', rtrim($_SERVER[REQUEST_URI], '/');
	  				if(ctype_digit($data[0])){
						echo getInfoComplaint($data[0]);
					}else if($data[0] == "hostel" || $data[0] == "individual" || $data[0] == "institute"){
						if(isset($_GET['scope'])){
							if(isset($_GET['status'])){
								echo getComplaintList($data[0],$_GET['scope'],$_GET['status'],"scope");
							}else{
			    				$status = "unresolved";
			    				echo getComplaintList($data[0],$_GET['scope'],$status,"status");
							}
						}else if(isset($_GET['userId'])){
							if(isset($_GET['status'])){
								echo getComplaintList($data[0],$_GET['userId'],$_GET['status'],"creatorId");
							}else{
			    				$status = "unresolved";
			    				echo getComplaintList($data[0],$_GET['userId'],$status,"creatorId");
							}
						}else{
							echo $errorResponse;
						}
    				}else{
    					echo $errorResponse;
    				}
    				break;
  				default:
				    echo $errorResponse;
    				break;
			}
		}
		else if(count($data) == 2){
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
		$addIssue = "INSERT INTO $tableName(title,description,scope,type,createdTime,creatorId,photoAvailable,status)".
						"VALUES('$title','$description','$scope','$type','$createdTime','$creatorId','$photoAvailable','unresolved')";
		$addIssueResult = $connection->query($addIssue);
		$result = array();
		$result["message"] = "submitSuccess"; 
		$complaintId = $connection->query("SELECT id from $tableName WHERE createdTime = '$createdTime'")->fetch_row()[0];
		
		if(isset($paramsArray["image"])){
			$base64Image = $paramsArray["image"];
			$imageData = base64_decode($base64Image);
			$source = imagecreatefromstring($imageData);
			$imageSave = imagejpeg($source,"../../images/".$complaintId.'.jpg',100);
			imagedestroy($source);
		}
		return json_encode(array("message" => "success", "complaintId" => $complaintId));
	}

	function getComplaintList($level,$scope,$status,$type){
		include('../connect_db.php');
		$query = "SELECT * from complaints WHERE type = '$level' AND $type = '$scope'";
		if($status != "all")
			$query .= " AND status = '$status'";
		$complaintListQuery = $connection->query($query);
		//print_r($complaintListQuery->num_rows);
		$complaintList = array('complaints' => array());
		for($i = 0; $i < $complaintListQuery->num_rows; $i++){
			$row = mysqli_fetch_assoc($complaintListQuery);
			$complaint = array(
								'id' => $row['id'],
								'title' => $row['title'],
								'description' => $row['description'],
								'scope' => $row['scope'],
								'type' => $row['type'],
								'status' => $row['status'],
								'createdTime' => $row['createdTime'],
								'creatorId' => $row['creatorId'],
								'photoAvailable' => $row['photoAvailable'],
							);
			$complaintList['complaints'][$i] = $complaint;
		}
		return json_encode($complaintList);
	}

	function getInfoComplaint($id){
		include('../connect_db.php');
		$query = "SELECT * from complaints WHERE id = '$id'";
		$complaintListQuery = $connection->query($query);
		$complaint = array();
		$complaint['complaintDetails'] = array();
		if($complaintListQuery->num_rows > 0){
			$row = mysqli_fetch_assoc($complaintListQuery);
			$complaint['complaintDetails'] = array(
								'id' => $row['id'],
								'title' => $row['title'],
								'description' => $row['description'],
								'scope' => $row['scope'],
								'type' => $row['type'],
								'status' => $row['status'],
								'createdTime' => $row['createdTime'],
								'creatorId' => $row['creatorId'],
								'photoAvailable' => $row['photoAvailable'],
							);
		}
		return json_encode($complaint);
	}
?>