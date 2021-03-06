GetFriendedBy Method
====================

Returns a list of users that have added the given user as a friend,
and current session information for each of those users.


Request Format
--------------

+-----------------+------------------------------------+--------+------------+
| *Parameter*     | *Description*                      | *Type* | *Required* |
+=================+====================================+========+============+
| `RequestMethod` | GetFriendedBy                      | String | Yes        |
+-----------------+------------------------------------+--------+------------+
| `UserID`        | Retrieve user and session          | UUID   | Yes        |
|                 | information for each user that has |        |            |
|                 | friended this given user           |        |            |
+-----------------+------------------------------------+--------+------------+

Sample request: ::

    RequestMethod=GetFriendedBy
    &UserID=efb00dbb-d4ab-46dc-aebc-4ba83288c3c0


Response Format
---------------

+--------------+------------------------------------------------+---------+
| *Parameter*  | *Description*                                  | *Type*  |
+==============+================================================+=========+
| `Success`    | True if a FriendedBy array was returned, False | Boolean |
|              | if a Message was returned                      |         | 
+--------------+------------------------------------------------+---------+
| `FriendedBy` | Array of friend objects, see below             | Array   | 
+--------------+------------------------------------------------+---------+
| `Message`    | Error message                                  | String  | 
+--------------+------------------------------------------------+---------+


Friend Object
-------------

+-------------------+---------------------------------------------+----------+
| *Parameter*       | *Description*                               | *Type*   |
+===================+=============================================+==========+
| `UserID`          | UUID for the user account                   | UUID     |
+-------------------+---------------------------------------------+----------+
| `Name`            | Account name                                | String   |
+-------------------+---------------------------------------------+----------+
| `Email`           | Account e-mail address                      | String   |
+-------------------+---------------------------------------------+----------+
| `AccessLevel`     | 0-255 value indicating the access level of  | Integer  |
|                   | this user. Described in more detail on the  |          |
|                   | AccessLevel page                            |          |
+-------------------+---------------------------------------------+----------+
| `SessionID`       | Current session identifier                  | UUID     |
+-------------------+---------------------------------------------+----------+
| `SecureSessionID` | Current session identifier that must only   | UUID     |
|                   | be transmitted across secure channels       |          |
+-------------------+---------------------------------------------+----------+
| `SceneID`         | UUID of the scene that the user is          | UUID     |
|                   | currently in                                |          |
+-------------------+---------------------------------------------+----------+
| `ScenePosition`   | Scene-relative current position of the user | Vector3d |
+-------------------+---------------------------------------------+----------+
| `SceneLookAt`     | Normalized direction vector where the user  | Vector3d |
|                   | is currently looking                        |          | 
+-------------------+---------------------------------------------+----------+
| `ExtraData`       | Free form JSON data attached to this user's | JSON     |
|                   | session                                     |          |
+-------------------+---------------------------------------------+----------+


Success: ::

    {
        "Success":true,
        "FriendedBy":
        [
            {
                "UserID":"153f5a45-8d4e-4b11-830a-133a966761fd",
                "Name":"Jane Doe",
                "Email":"jane.doe@email.com",
                "AccessLevel":1,
                "SessionID":"23e8bfa6-2123-4499-9670-9fe537e3e1f7",
                "SecureSessionID":"905e167a-0330-4b5c-9a1e-f940e2925303",
                "SceneID":"506e6610-1098-11df-8a39-0800200c9a66",
                "ScenePosition":[128,128,25],
                "SceneLookAt":[1,0,0],
                "ExtraData":{}
            },
            {
                "UserID":"dbe13051-f41d-4197-b600-db5fa00532e2",
                "Name":"Testarion Personhood",
                "Email":"testarion@email.com",
                "AccessLevel":0,
                "SessionID":"1eae1cb2-c11f-43a6-9e97-ad4af06d37e2",
                "SecureSessionID":"4f674961-cf02-400a-ba6f-ebe066756698",
                "SceneID":"506e6610-1098-11df-8a39-0800200c9a66",
                "ScenePosition":[128,128,50],
                "SceneLookAt":[0,1,0],
                "ExtraData":{}
            },
        ]
    }


Failure: ::

    {
        "Success":false,
        "Message":"User not found"
    }

