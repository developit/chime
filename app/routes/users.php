<?php

$app->get('/users/:username', $auth(0), function($username) use ($app) {

    // Find user
    $user = User::where('username', $username)
        ->withCount('followers', 'following', 'posts');

    // Check following status (authenticated)
    if ($app->authenticated) {
    	$user->with(['followers' => function ($query) use ($app) {
                $query->where('user_id', $app->user_id);
        }])
        ->with(['following' => function ($query) use ($app) {
                $query->where('follow_id', $app->user_id);
        }]);
    }

    // Get first result
    $user = $user->first();

    // Check if user exists
    if (!$user) {
        $app->notFound();
    }

	// Check following relationships (authenticated)
    if ($app->authenticated) {
	    $user->is_following();
	    $user->is_followed();
    }

    // User found, return profile
    echo json_encode($user);

});

$app->get('/users/:username/posts', $auth(0), $paginate, function($username) use ($app) {

    $user = User::where('username', $username)->first();

    if (!$user) {
        $app->notFound();
    }

    $posts = $user->posts()
        ->withCount('comments', 'likes')
		->when($app->authenticated, function ($query) use ($app) {
		        return $query->with([ 'likes' => function ($query) use ($app) {
                   return $query->where('user_id', $app->user_id);
        	}]);
		        })
        ->latest()
        ->skip($app->offset)
        ->take(20)
        ->get();

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


$app->get('/users/:username/comments', $auth(0), $paginate, function($username) use ($app) {

    $user = User::where('username', $username)->first();

    if (!$user) {
        $app->notFound();
    }

    $comments = $user->comments()
        ->withCount('likes')
		->when($app->authenticated, function ($query) use ($app) {
		        return $query->with([ 'likes' => function ($query) use ($app) {
                   return $query->where('user_id', $app->user_id);
        	}]);
		        })
        ->latest()
        ->skip($app->offset)
        ->take(20)->get();

    if (!$comments) {
        $app->halt(404, json_encode(['message' => 'There was an error']));
    }

     // Iterate and check if each comment is liked (authenticated)
    if ($app->authenticated) {
	    $comments->each(function($item) use ($app) {
	        $item->is_liked($app->user_id);
	    });  
    } 

    $app->halt(200, json_encode($comments));

});

// PUT: /users/[username]/follow
$app->put('/users/:username/follow', $auth(3), function($username) use ($app) {

    // Check user exists
    $user = User::where('username', $username)->first();

    if (!$user) {
        $app->notFound();
    }

    // Check user isn't following themselves
    if ($app->user_id == $user->id) {
        $app->halt(400, json_encode(['message' => 'You can\'t follow yourself']));
    }

    // Attach the follow
    $user->followers()->syncWithoutDetaching([$app->user_id]);

    // Notify the followed user
    $notification = Notification::updateOrCreate(['user_id' => $user->id, 'notifier_id' => $app->user_id, 'notification_type' => 'follow', 'object_type' => 'user']);

    $app->halt(200, json_encode(['message' => 'User followed']));
  
});

// DELETE: /users/[username]/follow
$app->delete('/users/:username/follow', $auth(3), function($username) use ($app) {

    // Check user exists
    $user = User::where('username', $username)->first();

    if (!$user) {
        $app->notFound();
    }

    // Check user isn't unfollowing themselves
    if ($app->user_id == $user->id) {
        $app->halt(400, json_encode(['message' => 'You can\'t unfollow yourself']));
    }

    // Attach the follow
    $user->followers()->detach($app->user_id);

    $app->halt(200, json_encode(['message' => 'User unfollowed']));

});

// GET: /users/[username]/followers
$app->get('/users/:username/followers', $auth(0), $paginate, function($username) use ($app) {

    // Find user
    $user = User::where('username', $username)->first();

    // Check if user exists
    if (!$user) {
        $app->notFound();
    }

    $followers = $user->followers()
        ->latest()
        ->skip($app->offset)
        ->take(20)->get();

    // User found, return profile
    echo json_encode($followers);

});

// GET: /users/[username]/following
$app->get('/users/:username/following', $auth(0), $paginate, function($username) use ($app) {

    // Find user
    $user = User::where('username', $username)->first();

    // Check if user exists
    if (!$user) {
        $app->notFound();
    }

    $following = $user->following()
        ->latest()
        ->skip($app->offset)
        ->take(20)->get();

    // User found, return profile
    echo json_encode($following);

});