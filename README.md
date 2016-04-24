#API Details :  
##/api/v1/auth/login
```
Before being able to use the app, the user should login.
Request Type : POST
POST parameters required: userName, password.
TODO : manage cookies.
```
###Response :
```json
{
  "message":"success",
  "userData": {
	  	"userId":"6",
	  	"firstName":"Chutiya",
	  	"lastName":"Kedia",
	  	"userName":"kchutiya",
	  	"hostel":"6",
	  	"userType":"student",
	  	"userType":"staff",
	  	"staffScope":"1"(only for staff)
  	}  
}  
### if email is not verified :
```json
{"message":"emailNotVerified"}
```
###if user not registered : 
```json
{"message":"invalidUserName"}
``` 
###if incorrect password
```json
{"message":"incorrectPassword"}
```  
### if the POST parameter is not valid, or there is some error
```json
{"message":"invalidRequest"}
```


##/api/v1/auth/logout
```
After logging out the cookie (that authenticates the user) is invalidated.
Request Type : GET
TODO : manage cookies.
```
###Response :
```json
{
   "success" : "true"
}
```
###if logout fails(due to some reason) : 
```json
{
   "success" : "false"
}
```
##/api/v1/users/list  
```
Returns list of all the registered users  
Request Type : GET  
```
###Response :  
```json
{
  "users": [
    {
      "userId": 1,
      "userType": "student"
    },
    {
      "userId": 2,
      "userType": "staff"
    },
    {
      "userId": 3,
      "userType": "faculty"
    }
  ],
  "message": "usersFound"
}
```  

###if no user found : 
```json
{"message":"noUsersFound"}
```  
  
##/api/v1/users/list/<userType>  
```
Returns list of all the registered users of type=<userType>(student,staff or faculty)  
Request Type : GET
```
###Response :  
```json
{
  "users": [
    1,
    5
  ],
  "message": "usersFound"
}  
```  
###if no user found :  
```json
{  
  "message":"noUsersFound"  
}  
```  

##/api/v1/users/user/<userId>  
```
Returns details of the user with given <userId>  
Request Type : GET
```
###Response :  
```json
{
  "user": {
    "userId": 1,
    "firstName": "Ashish",
    "lastName": "Ranjan",
    "userName": "tt1130908",
    "userType": "student",
    "hostel": 6,
    "isActivated": 1
  },
  "message": "userFound"
}  
```  
###for invalid userId :  
```json
{"message":"userNotFound"}
```  
  
  
##/api/v1/users/register/  
```
Registers a user
Request Type : POST
POST Parameters required  :firstName,lastName,userName,password,hostel,userType,scopeId(only for staffs)
```
###Response :  
```json
{"message":"success","userId":"6"}
```
###for invalid requests :  
```json
{"message":"invalidRequest"}
```



##/api/v1/complaint/<complaintId>/upvote or downvote/userId=<userId>  
```
To upvote/downvote complaint with given <complaintId>  
Request Type : GET
```
###Response :  
```json
{
  "complaintInfo": {
    "id": 1,
    "title": "Ashish",
    "description": "Ranjan",
    "scope": "tt1130908",
    "type": "student",
    "status": 6,
    "createdTime": 1,
    "timeAgo":"20 secs ago",
    "creatorId":2,
    "numComments":1,
    "upVotes":2,
    "downVotes":1,
    "photoAvailable":0
  },
  "message": "voteUpdated/voteNotUpdated/invalidRequest"
}  
```



##/api/v1/complaint/<complaintId>/updatestatus/status=<newStatus(resolved/unresolved)>  
```
To updatestatus of complaint with given <complaintId>  
Request Type : GET
```
###Response :  
```json
{
  "message": "statusUpdated/statusNotUpdated"
}  
```