API Details :

**/api/v1/users/list**  
Returns list of all the registered users  
Request Type : GET  
Response :  
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

if no user found : ```json{"message":"noUsersFound"}```  
  
**/api/v1/users/list/<userType>**  
Returns list of all the registered users of type=<userType>
Request Type : GET  
Response :  
```json
{
  "users": [
    1,
    5
  ],
  "message": "usersFound"
}  
```  
if no user found :  
```json
{  
  "message":"noUsersFound"  
}  
```  

**/api/v1/users/user/<userId>**  
Returns details of the user with given <userId>  
Request Type : GET  
Response :  
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
for invalid userId :  
```json
{"message":"userNotFound"}
```  
  
  
**/api/v1/users/register/**  
registers a user
Request Type : POST  
POST Parameters required : firstName,lastName,userName,password,hostel,userType,scopeId(only for staffs)  
Response :  
```json
{"message":"success","userId":"6"}
```
for invalid requests :  
```json
{"message":"invalidRequest"}
```