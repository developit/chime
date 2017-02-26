<?php

// GET: /hashtag/[hashtag]
$app->get('/hashtag/:hashtag', $auth(0), $paginateBefore, function($hashtag) use ($app) {

    // Get posts with specified hashtag
    $posts = Post::whereRaw('find_in_set(?, hashtags)', [$hashtag])
    ->withCount('comments', 'likes')
    ->with('user')
    ->when($app->authenticated, function ($query) use ($app) {
            return $query->with([ 'likes' => function ($query) use ($app) {
               return $query->where('user_id', $app->user_id);
        }]);
            })
    ->when($app->before_id, function($query) use ($app){
        return $query->where('id', '<', $app->before_id);
    })
    ->latest()->take(20)->get();

    // Check posts exist
    if (count($posts) <= 0) {
        $app->halt(404, json_encode(['message' => 'No posts exist with that hashtag']));
    }

    // Iterate and check if each post is liked (authenticated)
    if ($app->authenticated) {
        $posts->each(function($item) use ($app) {
            $item->is_liked($app->user_id);
        });
    }

    $app->halt(200, json_encode($posts)); 
  
});
