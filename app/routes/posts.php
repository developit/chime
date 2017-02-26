<?php

// GET: /posts/[id]
$app->get('/posts/:id', $auth(0), function($id) use ($app) {

    // Find post
    $post = Post::withCount('comments', 'likes')
        ->with('user')
        ->when($app->authenticated, function ($query) use ($app) {
                return $query->with([ 'likes' => function ($query) use ($app) {
                   return $query->where('user_id', $app->user_id);
            }]);
                })
        ->find($id);

    // 404 if not found
    if (!$post) {
        $app->notFound();
    }

    // Check if post is liked
    if ($app->authenticated) {
        $post->is_liked();
    }

    echo json_encode($post);
 
});

// GET: /posts/[id]/with_comments
$app->get('/posts/:id/with_comments', $auth(0), $paginateAfter, function($id) use ($app) {

    // Find post with comments
    $post = Post::withCount('comments', 'likes')
    ->when($app->authenticated, function ($query) use ($app) {
            return $query->with([ 'likes' => function ($query) use ($app) {
               return $query->where('user_id', $app->user_id);
        }]);
            })
    ->with(['user', 'comments' => function ($query) use ($app) {
            return $query->with('user')->withCount('likes')->take(20)
            ->when($app->authenticated, function ($query) use ($app) {
                return $query->with([ 'likes' => function ($query) use ($app) {
                   return $query->where('user_id', $app->user_id);
                }]);
            })
            ->when($app->after_id, function($query) use ($app){
                return $query->where('id', '>', $app->after_id);
            });
        }]) 
    ->find($id);

    if (!$post) {
        $app->notFound();
    }

    // Check if post is liked & if comments are liked
    if ($app->authenticated) {
        $post->is_liked();

        $post->comments->each(function($item) use ($app) {
            $item->is_liked($app->user_id);
        });  
    }  

    echo json_encode($post);
 
});


// POST /posts/create
$app->post('/posts/create', $auth(2), function() use ($app) {

     // Grab JSON body, make sure it's valid
    if (!$request = json_decode($app->request->getBody(), true)) {
        $app->halt(400, json_encode(['message' => 'Request body must be valid JSON']));
    }

    $post = new Post;

    // Validate post object
    if (!$post->validate($request)) {
        $app->halt(400, json_encode(['message' => 'Post creation failed, see errors', 'errors' => $post->errors()]));
    }

    // Populate post object
    $user = User::find($app->user_id);
    $post->body = $request['body'];

    // Try and save
    if (!$user->posts()->save($post)) {
        $app->halt(400, json_encode(['message' => 'Post creation failed, try again']));
    }

    // Success, tell the user
    else {
        echo json_encode(['message' => 'Post created successfully']);
    }

});

// DELETE: /posts/[id]
$app->delete('/posts/:id', $auth(2), function($id) use ($app) {

    // Check if posts exists
    $post = Post::find($id);

    // Post not found, error
    if (!$post) {
        $app->notFound();
    }

    // Check it belongs to current user
    if ($post->user_id != $app->user_id) {
        $app->halt(401, json_encode(['message' => 'You can only delete posts that you created']));
    }

    // Remove all likes
    $post->likes()->delete();

    // Remove all comments
    $post->comments()->delete();

    // Remove post
    $post->delete();

    // Tell the user it's deleted
    $app->halt(200, json_encode(['message' => 'Post deleted!']));
  
});


// POST /posts/[id]/comment
$app->post('/posts/:id/comment', $auth(2), function($id) use ($app) {

    // Grab JSON body, make sure it's valid
    if (!$request = json_decode($app->request->getBody(), true)) {
        $app->halt(400, json_encode(['message' => 'Request body must be valid JSON']));
    }

    // Find post to comment on
    $post = Post::find($id);

    // 404 if not found
    if (!$post) {
        $app->notFound();
    }

    $comment = new Comment;

    // Validate comment object
    if (!$comment->validate($request)) {
        $app->halt(400, json_encode(['message' => 'Comment creation failed, see errors', 'errors' => $comment->errors()]));
    }

    // Populate comment object
    $comment->body = $request['body'];
    $comment->user_id = $app->user_id;

    // Try to save
    if(!$post->comments()->save($comment)) {
        $app->halt(400, json_encode(['message' => 'Comment creation failed, try again']));
    }

    // Notify the post owner about the comment (if it wasn't their own post)
    if ($post->user_id != $app->user_id) {
        $notification = Notification::create(['user_id' => $post->user_id, 'notifier_id' => $app->user_id, 'notification_type' => 'comment', 'object_type' => 'post', 'object_id' => $post->id]);
    }

    // Success, tell the user
    echo json_encode(['message' => 'Comment created successfully']);

});

// PUT: /posts/[id]/like
$app->put('/posts/:id/like', $auth(2), function($id) use ($app) {

    // Check if posts exists
    $post = Post::with(['likes' => function ($query) use ($app) {
                $query->where('user_id', $app->user_id);
        }])->find($id);

    // Post not found, error
    if (!$post) {
        $app->notFound();
    }

    // Set is_liked property
    $post->is_liked();

    // Like the post if not already liked
    if (!$post->is_liked) { 
        $like = new Like(['user_id' => $app->user_id]);
        $liked = $post->likes()->save($like);
    }

    // Notify the post owner about the like (if it wasn't their own post)
    if ($post->user_id != $app->user_id) {
        $notification = Notification::updateOrCreate(['user_id' => $post->user_id, 'notifier_id' => $app->user_id, 'notification_type' => 'like', 'object_type' => 'post', 'object_id' => $post->id]);
    }    

    // Tell the user it's liked
    $app->halt(200, json_encode(['message' => 'Post liked!']));
  
});

// DELETE: /posts/[id]/like
$app->delete('/posts/:id/like', $auth(2), function($id) use ($app) {

    // Check if posts exists
    $post = Post::find($id);

    // Post not found, error
    if (!$post) {
        $app->notFound();
    }

    // Remove any likes for this post + user combo
    $post->likes()->where('user_id', $app->user_id)->delete();

    // Tell the user it's unliked
    $app->halt(200, json_encode(['message' => 'Post unliked!']));
  
});
