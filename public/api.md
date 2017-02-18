chime is a social network in the form of a RESTful API. You can interact with the network using the endpoints outlined below. **Note:** Some `/users/` and `/posts/` GET endpoints below make the use of an auth token optional, for public access to these resources. This means you can access a post with ID: 1 publicly without any authentication required at /posts/1. 

Developer? You can browse the open-source code @ https://github.com/ummjackson/chime

Have questions about the API? Chat with fellow developers @ https://discord.gg/PtdN8hJ

## API Endpoints

### Register a new account <span class="level none"></span>

- POST /account/register
- Accept: application/json
- Body:

```json                                                                                                                     
{
    "username": "example", // Required, alpha-numeric between 1 and 32 characters
    "password": "password", // Required
    "name": "Example User", // Required, between 1 and 100 characters
    "email": "test@example.com" // Required, valid email
}
```

Email is required for Gravatar avatar image and password recovery (to be implemented!)

### Authorize access to an account (ie. login) <span class="level none"></span>

- POST /account/authorize
- Accept: application/json
- Body:

```json                                                                                                                     
{
    "username": "example", // Required
    "password": "password" // Required
    "description": "My Chime App" // Required, alpha-numeric + spaces, between 1 and 100 characters
    "access_level": 2 // Required, integer between 1 and 3 as per below access levels
}
```

Returns a JWT **access_token** to be sent via the Authorization header for all requests requiring authoriziation.

A description of your app and numeric access level must be provided. Access levels are as follows:

1. Read
2. Read/Write
3. Read/Write & Account/Profile Settings

### Get All Public Posts <span class="level one"></span>

- GET /public
- Accept: application/json
- Authorization: Bearer [access_token]. *Optional*

Returns the latest public posts from all users across the network. Will return 20 posts. Results can be paged through using the ?offset= parameter. For example, ?offset=20 and so forth.

### Get Personalized Timeline <span class="level one"></span>

- GET /timeline
- Accept: application/json
- Authorization: Bearer [access_token]

Returns the authorized user's timeline, including posts from every account they have followed. Will return 20 posts. Results can be paged through using the ?offset= parameter. For example, ?offset=20 and so forth.

### Get Notifications <span class="level one"></span>

- GET /notifications
- Accept: application/json
- Authorization: Bearer [access_token]

Returns the authorized user's stream of notifications including follows, likes and comments. Will return 20 latest notifications. Results can be paged through using the ?offset= parameter. For example, ?offset=20 and so forth.

### Get Posts Containing a Hashtag <span class="level one"></span>

- GET /hashtag/[hashtag]
- Accept: application/json
- Authorization: Bearer [access_token] *Optional*

Returns all posts containing the given hashtag. Results can be paged through using the ?offset= parameter. For example, ?offset=20 and so forth. 

### Get User Profile <span class="level one"></span>

- GET /users/[username]
- Accept: application/json
- Authorization: Bearer [access_token] *Optional*

Returns the given user's profile

### Get User Posts <span class="level one"></span>

- GET /users/[username]/posts
- Accept: application/json
- Authorization: Bearer [access_token] *Optional*

Returns the given user's last 20 posts. Results can be paged through using the ?offset= parameter. For example, ?offset=20 and so forth. 

### Get User Comments <span class="level one"></span>

- GET /users/[username]/comments
- Accept: application/json
- Authorization: Bearer [access_token] *Optional*

Returns the given user's last 20 comments. Results can be paged through using the ?offset= parameter. For example, ?offset=20 and so forth.

### Get User's Followers <span class="level one"></span>

- GET /users/[username]/followers
- Accept: application/json
- Authorization: Bearer [access_token] *Optional*

Returns the users follwing the specified user, in blocks of 20. Results can be paged through using the ?offset= parameter. For example, ?offset=20 and so forth.

### Get User's Followed Users <span class="level one"></span>

- GET /users/[username]/following
- Accept: application/json
- Authorization: Bearer [access_token] *Optional*

Returns a list of users the current user is following, in blocks of 20. Results can be paged through using the ?offset= parameter. For example, ?offset=20 and so forth.

### Get Post <span class="level one"></span>

- GET /posts/[id]
- Accept: application/json
- Authorization: Bearer [access_token] *Optional*

Returns the specified post

### Get Post (with comments) <span class="level one"></span>

- GET /posts/[id]/with_comments
- Accept: application/json
- Authorization: Bearer [access_token] *Optional*

Returns the specified post, with the first 20 associated comments. Comments can be paged through using the ?offset= parameter. For example, ?offset=20 and so forth.

### Create New Post <span class="level two"></span>

- POST /posts/create
- Accept: application/json
- Authorization: Bearer [access_token]
- Body:

```json                                                                                                                     
{
    "body": "My first Chime post!" // Required, max. 200 characters
}
```

### Delete A Post <span class="level two"></span>

- DELETE /posts/[post_id]
- Accept: application/json
- Authorization: Bearer [access_token]

Delete a post created by the current user.

### Like A Post <span class="level two"></span>

- PUT /posts/[post_id]/like
- Accept: application/json
- Authorization: Bearer [access_token]

Likes the post from the current account.

### Unike A Post <span class="level two"></span>

- DELETE /posts/[post_id]/like
- Accept: application/json
- Authorization: Bearer [access_token]

Unlikes the post from the current account.

### Create New Comment <span class="level two"></span>

- POST /posts/[post_id]/comment
- Accept: application/json
- Authorization: Bearer [access_token]
- Body:

```json                                                                                                                     
{
    "body": "My first comment!"" // Required, max. 200 characters
}
```

### Get Comment <span class="level one"></span>

- GET /comments/[id]
- Accept: application/json
- Authorization: Bearer [access_token] *Optional*

Returns the specified comment

### Delete A Comment <span class="level two"></span>

- DELETE /comments/[comments_id]
- Accept: application/json
- Authorization: Bearer [access_token]

Delete a comment created by the current user.

### Like A Comment <span class="level two"></span>

- PUT /comments/[comment_id]/like
- Accept: application/json
- Authorization: Bearer [access_token]

Likes the comment from the current account.

### Unike A Comment <span class="level two"></span>

- DELETE /comments/[comment_id]/like
- Accept: application/json
- Authorization: Bearer [access_token]

Unlikes the comment from the current account.

### Follow User <span class="level three"></span>

- PUT /users/[username]/follow
- Accept: application/json
- Authorization: Bearer [access_token]

Follows the specified user from the current account.

### Unfollow User <span class="level three"></span>

- DELETE /users/[username]/follow
- Accept: application/json
- Authorization: Bearer [access_token]

Follows the specified user from the current account.

### Update Profile <span class="level three"></span>

- POST /account/profile
- Accept: application/json
- Authorization: Bearer [access_token]
- Body:

```json                                                                                                                     
{
    "name": "My first comment!", // Optional, between 1 and 100 characters
    "location": "My first comment!"" // Optional, alpha-numeric and spaces, between 1 and 32 characters
    "bio": "My first comment!"" // Optional, between 1 and 200 characters
    "website": "My first comment!"" // Optional, must be valid URL including http(s)://
    "color": "My first comment!"" // Optional, six character hex color code (for profile personalization)
}
```

Updates the authorized user's profile. All fields are optional, only those provided in JSON body will be updated.

### View Access Tokens <span class="level three"></span>

- GET /account/tokens
- Accept: application/json
- Authorization: Bearer [access_token]

Returns a list of currently active JWT access tokens.

### Delete Access Token <span class="level three"></span>

- DELETE /account/tokens/[token_id]
- Accept: application/json
- Authorization: Bearer [access_token]

Deletes the specified JWT access token.

