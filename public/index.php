<?php

if (PHP_SAPI == 'cli-server') {
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

// Load dependencies
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Database
require __DIR__ . '/../app/config/database.php';

// Prepare app
$app = new \Slim\Slim(array(
    'templates.path' => __DIR__ . '/../app/templates',
));

// Prepare view
$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = array(
    'charset' => 'utf-8',
    'cache' => realpath(__DIR__ . '/../app/templates/cache'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension());

// Set JWT key
require __DIR__ . '/../app/config/jwt.php';

// Default route conditions
\Slim\Route::setDefaultConditions(array(
    'username' => '[a-zA-Z0-9_]{1,32}',
    'hashtag' => '[a-zA-Z0-9]{1,200}'
));

// Pagination middleware (?before_id=[id])
$paginateBefore = function () use ($app) {
    $before_id = $app->request()->params('before_id');
    if (is_numeric($before_id) && $before_id >= 0) {
        $app->before_id = $before_id;
    }
};

// Pagination middleware (?after_id=[id])
$paginateAfter = function () use ($app) {
    $after_id = $app->request()->params('after_id');
    if (is_numeric($after_id) && $after_id >= 0) {
        $app->after_id = $after_id;
    }
};

// Pagination middleware (?offset=)
$paginateOffset = function () use ($app) {
    $offset = $app->request()->params('offset');
    if (is_numeric($offset) && $offset >= 0) {
        $app->offset = $offset;
    }
    else {
        $app->offset = 0;
    }
};

// Auth enforcement middleware
$auth = function ($access_level = 0) use ($app) {
    return function () use ($access_level, $app) {

        // Allow for unauthenticated / authenticated combo routes (Access Level 0)
        if ($access_level == 0 && !$app->request->headers->get('Authorization'))
        {
            $app->authenticated = false;
        }

        // All other access levels, including Level 0 but attaching creds if present
        else
        {

            if(!$app->request->headers->get('Authorization')) {
               $app->halt(401, json_encode(['message' => 'Access token must be present'])); 
            }

            list($token) = sscanf($app->request->headers->get('Authorization'), 'Bearer %s');

            $jwt = new Token;

            // Validate JWT
            $tokenValid = $jwt->decode($token);

            // Invalid, fail
            if (!$tokenValid) {
                $app->halt(401, json_encode(['message' => 'Access token must be valid']));
            }

            // Find in database
            $tokenExists = Token::find($tokenValid->jti);

            // Doesn't exist in database or id/token mismatch
            if (!$tokenExists || $tokenExists->token != hash('md5', $token)) {
                $app->halt(401, json_encode(['message' => 'Access token must be valid']));
            }

            // Access level is not sufficient
            if ($tokenExists->access_level < $access_level) {
                $app->halt(401, json_encode(['message' => 'Insufficient access level to perform that action']));
            }

            // Everything looks good, proceed        
            $app->authenticated = true;
            $app->access_level = $tokenExists->access_level;
            $app->user_id = $tokenExists->user_id;
            $app->token_id = $tokenExists->id;

        }      
    };

};

// 404 Not Found Handler
$app->notFound(function () use ($app) {
    $app->contentType('application/json');
    $app->halt(404, json_encode(['message' => 'Endpoint or resource not found']));
});

// 500 Error/Exception Handler
$app->error(function (\Exception $e) use ($app) {
    $app->contentType('application/json');
    $app->halt(500, json_encode(['message' => 'Something went wrong, check that the service is available and try again']));
});

// Set global response headers (can be overwritten in actual route if needed)
$app->hook('slim.before.dispatch', function () use ($app) {
    $app->contentType('application/json');
});

// Enable CORS for all routes
$app->add(new \CorsSlim\CorsSlim());

// GET: /
$app->get('/', function () use ($app) {
    $app->contentType('text/html;charset=UTF-8');
    $app->render('index.html');
});

// Routes
require __DIR__ . '/../app/routes/account.php';
require __DIR__ . '/../app/routes/users.php';
require __DIR__ . '/../app/routes/posts.php';
require __DIR__ . '/../app/routes/comments.php';
require __DIR__ . '/../app/routes/hashtags.php';

// Run app
$app->run();