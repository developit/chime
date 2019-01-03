<?php
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$DATABASE_URL = parse_url(getenv("DATABASE_URL"));

/**
 * Update below with your MySQL DB settings (don't change database name unless you updated schema.sql)
 */
$capsule->addConnection([
    'driver' => 'pgsql',
    'host' => $DATABASE_URL["host"],
    'port' => $DATABASE_URL["port"],
    'database' => ltrim($DATABASE_URL["path"], "/"),
    'username' => $DATABASE_URL["user"],
    'password' => $DATABASE_URL["pass"],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'sslmode' => 'require',
    'prefix' => '',
]);

// Make this Capsule instance available globally via static methods
$capsule->setAsGlobal();

// Setup the Eloquent ORM
$capsule->bootEloquent();
?>
