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
			// switch case over type of requests
			switch ($method) {
  				case 'POST':
  					// registering a complaint initial checks
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
  					// viewing complaints initial checks.
  					//print_r(expression)
  					//$params = explode('?', rtrim($_SERVER[REQUEST_URI], '/');
	  				if(ctype_digit($data[0]) && isset($_GET['voterId'])){
						echo getInfoComplaint($data[0],$_GET['voterId']);
					}else if(($data[0] == "hostel" || $data[0] == "individual" || $data[0] == "institute")
							&& isset($_GET['voterId'])){
						if(isset($_GET['scope'])){
							if(isset($_GET['status'])){
								echo getComplaintList($data[0],$_GET['scope'],$_GET['status'],"scope",$_GET['voterId']);
							}else{
			    				$status = "unresolved";
			    				echo getComplaintList($data[0],$_GET['scope'],$status,"scope",$_GET['voterId']);
							}
						}else if(isset($_GET['userId'])){
							if(isset($_GET['status'])){
								echo getComplaintList($data[0],$_GET['userId'],$_GET['status'],"creatorId",$_GET['voterId']);
							}else{
			    				$status = "unresolved";
			    				echo getComplaintList($data[0],$_GET['userId'],$status,"creatorId",$_GET['voterId']);
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
			$complaintId = $data[0];
			if(ctype_digit($complaintId) && isset($_GET['userId'])){
				switch($data[1]){
					// upvote, downvote
					case "upvote":
						changeVote($complaintId,$_GET['userId'],1);
						break;
					case "downvote":
						changeVote($complaintId,$_GET['userId'],-1);
						break;
				}
			}else if(ctype_digit($complaintId) && $data[1] == "comment"){
				switch ($_SERVER['REQUEST_METHOD']) {
					case 'POST':
						$paramsArray = (array)json_decode(file_get_contents("php://input"));
						if(!isset($paramsArray["userId"]) || 
							!isset($paramsArray["userName"]) ||
							!isset($paramsArray["comment"])){
							echo $invalidRequest;
						}else{
							addComment($complaintId,$paramsArray);
						}
						break;
					case 'GET':
						echo fetchComments($complaintId);
						break;
					default:
						break;
				}
			}else{
				echo $errorResponse;
			}
		}else{
			echo $errorResponse;
		}
	}else{
		echo json_encode(array("message"=>"invalidRequest"));
	}

	// function adds comment to db using complaint id.
	function addComment($complaintId,$paramsArray){
		include('../connect_db.php');
		$userId = $paramsArray["userId"];
		$userName = $paramsArray["userName"];
		$comment = $connection->real_escape_string($paramsArray["comment"]);
		$createdTime = date("d-m-Y H:i:s");
		$query = "INSERT INTO comments(complaintId,userId,userName,comment,createdTime) VALUES('$complaintId','$userId','$userName','$comment','$createdTime')";
		$queryTotal = "UPDATE complaints SET numComments = numComments + 1 WHERE id='$complaintId'";
		$add = $connection->query($query);
		$addToTotal = $connection->query($queryTotal);
		if($add && $addToTotal)
			echo json_encode(array("message"=>"commentAdded"));
		else
			echo json_encode(array("message"=>"commentNotAdded"));
	}
	// function fetches from db complaint with complaintId
	function fetchComments($complaintId){
		include('../connect_db.php');
		$query = "SELECT * FROM comments WHERE complaintId = '$complaintId'";
		$fetch = $connection->query($query);
		$comments = array('comments' => array() );
		for($i = 0; $i < $fetch->num_rows; $i++){
			$row = mysqli_fetch_assoc($fetch);
			$timeAgo = getTimeDifference($row["createdTime"]);
			$comment = array(
				'id' => $row["id"],
				'userId' => $row["userId"],
				'userName' => $row["userName"],
				'comment' => $row["comment"],
				'createdTime' => $row["createdTime"],
				'timeAgo' => $timeAgo
				);
			$comments["comments"][$i] = $comment;
		}
		return json_encode($comments);
	}
	// function for registering complaint, storing on db., also stores image in base64 format.
	function registerComplaint($paramsArray, $compType){
		include('../connect_db.php');
		$title = $connection->real_escape_string($paramsArray["title"]);
		$description = $connection->real_escape_string($paramsArray["description"]);
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

	function getComplaintList($level,$scope,$status,$type,$voterId){
		include('../connect_db.php');
		$query = "SELECT * from complaints WHERE type = '$level' AND $type = '$scope'";
		if($status != "all")
			$query .= " AND status = '$status'";
		$complaintListQuery = $connection->query($query);
		//print_r($complaintListQuery->num_rows);
		$complaintList = array('complaints' => array());
		for($i = 0; $i < $complaintListQuery->num_rows; $i++){
			$row = mysqli_fetch_assoc($complaintListQuery);
			$timeAgo = getTimeDifference($row["createdTime"]);
			$complaint = array(
								'id' => $row['id'],
								'title' => $row['title'],
								'description' => $row['description'],
								'scope' => $row['scope'],
								'type' => $row['type'],
								'status' => $row['status'],
								'createdTime' => $row['createdTime'],
								'timeAgo' => $timeAgo,
								'creatorId' => $row['creatorId'],
								'numComments' => $row['numComments'],
								'upVotes' => $row['upVotes'],
								'downVotes' => $row['downVotes'],
								'photoAvailable' => $row['photoAvailable'],
							);
			$check = checkVote($voterId,$row['id'],$connection);
			if($check->num_rows > 0){
				$complaint['vote'] = mysqli_fetch_assoc($check)['voteType'];
			}else{
				$complaint['vote'] = 0;
			}
			$complaintList['complaints'][$i] = $complaint;
		}
		return json_encode($complaintList);
	}
	function getTimeDifference($createdTime){
		$diff = abs(strtotime(date("d-m-Y H:i:s")) - strtotime($createdTime));
		$years = floor($diff / (365*60*60*24));
		$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
		$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
		$hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 -$days*(60*60*24))/ (60*60));
		$minutes = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 -$days*(60*60*24) - $hours*60*60)/ (60));
		$seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 -$days*(60*60*24) - $hours*60*60 - $minutes*60));
		$stringDiff = "";
		if($years > 0){
			if($year > 1)
				$stringDiff .= $years." years, ";
			else
				$stringDiff .= $years." year, ";
		}
		if($months > 0){
			if($months > 1)
				$stringDiff .= $months." months, ";
			else
				$stringDiff .= $months." month, ";
		}
		if($days > 0 && $years==0){
			if($days > 1)	
				$stringDiff .= $days." days, ";
			else
				$stringDiff .= $days." day, ";
		}
		if($hours > 0 && $years==0){
			if($hours > 1)	
				$stringDiff .= $hours." hours, ";
			else
				$stringDiff .= $hours." hour, ";
		}
		if($minutes > 0 && $days==0){
			if($minutes > 1)
				$stringDiff .= $minutes." minutes, ";
			else
				$stringDiff .= $minutes." minute, ";
		}
		if($seconds > 0 && $hours==0){
			if($seconds > 1)
				$stringDiff .= $seconds." seconds";
			else
				$stringDiff .= $seconds." second";
		}
		$stringDiff = rtrim(rtrim($stringDiff," "),",");
		$stringDiff .= " ago";
		return $stringDiff;
	}
	function getInfoComplaint($id){
		include('../connect_db.php');
		$query = "SELECT * from complaints WHERE id = '$id'";
		$complaintListQuery = $connection->query($query);
		$complaint = array();
		$complaint['complaintDetails'] = array();
		if($complaintListQuery->num_rows > 0){
			$row = mysqli_fetch_assoc($complaintListQuery);
			$timeAgo = getTimeDifference($row["createdTime"]);
			$complaint['complaintDetails'] = array(
								'id' => $row['id'],
								'title' => $row['title'],
								'description' => $row['description'],
								'scope' => $row['scope'],
								'type' => $row['type'],
								'status' => $row['status'],
								'createdTime' => $row['createdTime'],
								'timeAgo' => $timeAgo,
								'creatorId' => $row['creatorId'],
								'numComments' => $row['numComments'],
								'upVotes' => $row['upVotes'],
								'downVotes' => $row['downVotes'],
								'photoAvailable' => $row['photoAvailable'],
							);
			$check = checkVote($voterId,$row['id'],$connection);
			if($check->num_rows > 0){
				$complaint['vote'] = mysqli_fetch_assoc($check)['voteType'];
			}else{
				$complaint['vote'] = 0;
			}
		}
		return json_encode($complaint);
	}
	function checkVote($userId, $complaintId,$connection){
		$query = "SELECT * from votes WHERE userId = '$userId' AND complaintId='$complaintId'";
		$check = $connection->query($query);
		return $check;
	}
	// function allows user to only upvote or downvote exactly once.
	function changeVote($complaintId,$userId,$vote){
		include('../connect_db.php');
		if(checkVote($userId,$complaintId,$connection)->num_rows > 0){
			if(mysqli_fetch_assoc($check)['voteType'] == $vote){
				//echo json_encode(array('message' => 'alreadyVoted'));
				$update = $connection->query("DELETE FROM votes WHERE userId='$userId' AND complaintId='$complaintId'");
				if($update){
					$query = "UPDATE complaints SET upVotes = upVotes - 1 WHERE id='$complaintId'";
					if($vote == -1){
						$query = "UPDATE complaints SET downvotes = downvotes - 1 WHERE id='$complaintId'";
					}
					$update = $connection->query($query);
					if($update) echo json_encode(array('message' => 'voteUpdated'));
					else echo json_encode(array('message' => 'voteNotUpdated'));
				}
			}else{
				$update = $connection->query("UPDATE votes SET voteType= '$vote' WHERE userId='$userId' AND complaintId='$complaintId'");
				if($update){
					$query = "UPDATE complaints SET upVotes = upVotes + 1,downvotes = downvotes - 1 WHERE id='$complaintId'";
					if($vote == -1){
						$query = "UPDATE complaints SET upVotes = upVotes - 1,downvotes = downvotes + 1 WHERE id='$complaintId'";
					}
					$update = $connection->query($query);
					if($update) echo json_encode(array('message' => 'voteUpdated'));
					else echo json_encode(array('message' => 'voteNotUpdated'));
				}
				else{
					echo json_encode(array('message' => 'voteNotUpdated'));
				}
			}
		}else{
			$query = "INSERT INTO votes(complaintId,userId,voteType) VALUES('$complaintId','$userId','$vote')";
			$update = $connection->query($query);
			if($update){
					$query = "UPDATE complaints SET upVotes = upVotes + 1 WHERE id='$complaintId'";
					if($vote == -1){
						$query = "UPDATE complaints SET downvotes = downvotes + 1 WHERE id='$complaintId'";
					}
					$update = $connection->query($query);
					if($update) echo json_encode(array('message' => 'voteUpdated'));
					else echo json_encode(array('message' => 'voteNotUpdated'));
			}else{
				echo json_encode(array('message' => 'voteNotUpdated'));
			}
		}
	}
?>