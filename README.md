API Details :

/api/v1/users/list  
Returns list of all the registered users  
Request Type : GET  
Response :  
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
    },
    {
      "userId": 4,
      "userType": "faculty"
    },
    {
      "userId": 5,
      "userType": "student"
    }
  ],
  "message": "usersFound"
}
  
  
/api/v1/users/list/<userType>  
Returns list of all the registered users of type=<userType>
Request Type : GET  
Response :  
{
  "users": [
    1,
    5
  ],
  "message": "usersFound"
}  
  
  

/api/v1/users/user/<userId>  
Returns detailsof the user with given <userId>  
Request Type : GET  
Response :  
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