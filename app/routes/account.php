<?php

// GET: /public
$app->get('/public', $auth(0), $paginate, function() use ($app) {
    
    $posts = Post::withCount('comments', 'likes')
    ->with('user')
    ->when($app->authenticated, function ($query) use ($app) {
            return $query->with([ 'likes' => function ($query) use ($app) {
               return $query->where('user_id', $app->user_id);
        }]);
            })
    ->latest()->skip($app->offset)->take(20)->get();

    if (!$posts) {
        $app->halt(404, json_encode(['message' => 'There was an error']));
    }

    // Iterate and check if each post is liked (authenticated)
    if ($app->authenticated) {
        $posts->each(function($item) use ($app) {
            $item->is_liked($app->user_id);
        });
    }

    $app->halt(200, json_encode($posts)); 

});

// POST: /account/register
$app->post('/account/register', function () use ($app) {

    // Grab JSON body, make sure it's valid
    if (!$request = json_decode($app->request->getBody(), true)) {
        $app->halt(400, json_encode(['message' => 'Request body must be valid JSON']));
    }

    // Create new User object
    $user = new User;

    // Validate User request against rules
    if(!$user->validate($request)) {
        $app->halt(400, json_encode(['message' => 'Account creation failed, see errors', 'errors' => $user->errors()]));
    }

    // Check if username already exists
    if ($user->usernameExists($request['username'])) {
        $app->halt(400, json_encode(['message' => 'Username is already taken, try another']));
    }

    // Set user object properties
    $user->username = $request['username'];
    $user->name = $request['name'];
    $user->email = $request['email'];
    $user->password = $request['password'];
 
    // Try to save, fail if there is an issue       
    if(!$user->save()) {
        $app->halt(400, json_encode(['message' => 'There was a problem creating the account, try again']));
    }

    // Success, account created!
    else {
        $app->halt(200, json_encode(['message' => 'Account created, you can now start using your account']));
    }

});

// POST: /account/authorize
$app->post('/account/authorize', function() use ($app) {

    // Grab JSON body, make sure it's valid
    if (!$request = json_decode($app->request->getBody(), true)) {
        $app->halt(400, json_encode(['message' => 'Request body must be valid JSON']));
    }

    // Make sure username/password are present
    if (!$request['username'] || !$request['password']) {
      $app->halt(400, json_encode(['message' => 'You must provide username/password']));  
    }

    $user = User::where('username', $request['username'])->first();

    // Check user exists
    if (!$user) {
        $app->halt(400, json_encode(['message' => 'Authorization failed, username/password incorrect']));
    }

    // Check password is correct
    if (!password_verify($request['password'], $user->password)) {
        $app->halt(400, json_encode(['message' => 'Authorization failed, username/password incorrect']));
    }

    // Create a new token
    $token = new Token;

    // Validate that we have description + access_level
    if (!$token->validate($request)) {
        $app->halt(400, json_encode(['message' => 'Authorization failed, see errors', 'errors' => $token->errors()]));
    }

    // Generate a token
    $encoded = $token->generate($user, $request['description'], $request['access_level']);

    // Set token properties
    $token->id = $encoded['id'];
    $token->token = hash('md5', $encoded['token']);
    $token->description = $request['description'];
    $token->access_level = $request['access_level'];
    $token->created_ip = $_SERVER['REMOTE_ADDR'];

    // Try to save, confirm it
    if (!$user->tokens()->save($token)) {
        $app->halt(400, json_encode(['message' => 'Authorization failed, try again']));
    }

    // Success, give them the token!
    else {
        $app->halt(200, json_encode(['message' => 'Authorization successful, access token granted', 'user' => $user, 'access_token' => $encoded['token']]));
    }    

});

// GET: /timeline
$app->get('/timeline', $auth(1), $paginate, function() use ($app) {
    
    $posts = Post::withCount('comments', 'likes')
    ->with('user')
    ->with(['likes' => function ($query) use ($app) {
            $query->where('user_id', $app->user_id);
    }])
    ->whereIn('user_id', function($query) use ($app)
    {
      $query->select('follow_id')
            ->from('follows')
            ->where('user_id', $app->user_id);
    })->orWhere('user_id', $app->user_id)->latest()->skip($app->offset)->take(20)->get();

    if (!$posts) {
        $app->halt(404, json_encode(['message' => 'There was an error']));
    }

    // Iterate and check if each post is liked
    $posts->each(function($item) use ($app) {
        $item->is_liked($app->user_id);
    });

    $app->halt(200, json_encode($posts)); 

});

// GET: /notifications
$app->get('/notifications', $auth(1), $paginate, function() use ($app) {

    $user = User::find($app->user_id);

    $notifications = $user->notifications()
        ->with('user')->latest()->skip($app->offset)->take(20)->get();

    if (!$notifications) {
        $app->halt(404, json_encode(['message' => 'There was an error']));
    }

    $app->halt(200, json_encode($notifications)); 

});


// POST: /account/profile
$app->post('/account/profile', $auth(3), function() use ($app) {

    // Grab JSON body, make sure it's valid
    if (!$request = json_decode($app->request->getBody(), true)) {
        $app->halt(400, json_encode(['message' => 'Request body must be valid JSON']));
    }

    // Find user
    $user = User::find($app->user_id);

    // 404 if not found
    if (!$user) {
        $app->notFound();
    }

    // Validate User request against rules
    if(!$user->validate($request, 'update')) {
        $app->halt(400, json_encode(['message' => 'Profile update failed, see errors', 'errors' => $user->errors()]));
    }

    // Populate the object
    $user->name = $request['name'] ?? $user->name;
    $user->location = $request['location'] ?? $user->location;
    $user->bio = $request['bio'] ?? $user->bio;
    $user->website = $request['website'] ?? $user->website;
    $user->color = $request['color'] ?? $user->color;

    // Try to save, fail if there is an issue       
    if(!$user->save()) {
        $app->halt(400, json_encode(['message' => 'There was a problem updating your profile, try again']));
    }

    // Success, account updated!
    else {
        $app->halt(200, json_encode(['message' => 'Profile updated!!']));
    }


});

// GET: /account/tokens
$app->get('/account/tokens', $auth(3), function() use ($app) {
    
    $tokens = Token::where('user_id', $app->user_id)->get();

    if (!$tokens) {
        $app->halt(404, json_encode(['message' => 'There was an error']));
    }

    $app->halt(200, json_encode($tokens)); 

});

// DELETE: /account/tokens/[token_id]
$app->delete('/account/tokens/:token', $auth(3), function($token_id) use ($app) {

    $token = Token::find($token_id);

    if (!$token) {
        $app->halt(404, json_encode(['message' => 'Token not found']));
    }

    if ($token->user_id != $app->user_id) {
        $app->halt(400, json_encode(['message' => 'You can only delete tokens that belong to your account']));
    }

    if ($token->delete()) {
        $app->halt(200, json_encode(['message' => 'Token removed successfully']));
    }

});

// TBD: POST /account/settings