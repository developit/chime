![chime logo](logo.png?raw=true)

chime is a social network in the form of a RESTful API. Currently under development, use at your own risk!

There is a running instance of chime you can play with @ https://chime.social

Have questions? Chat with fellow developers @ https://discord.gg/PtdN8hJ

## Developing

Tested on macOS Sierra and Ubuntu 16.04 running PHP 7.0.x and up.

####1. First, clone the repo:

`git clone https://github.com/ummjackson/chime.git`

####2. Make sure you have a MySQL instance running, then navigate to the root directory of the project and enter the `mysql` CLI tool and run:

`source app/schema.sql`

This will create the needed database + tables for you.

####3. Make sure you have [Composer](https://getcomposer.org/) installed, then from the root directory of the project, run:

`composer install`

####4. Next, open up `/config/database.php` and `/config/jwt.php` to update your database + secret key settings. 

####5. Once the settings are updated, start the app from the root directory using PHP's built-in server with:

`php -S 0.0.0.0:8080 -t public public/index.php`

This will run an instance of the API on Port 8080. Navigate to http://0.0.0.0:8080 to view the API documentation and get started.

## Running on Production

It is recommended that for production purposes, you run PHP-FPM with a reverse proxy such as Nginx in front of it.

## The MIT License (MIT)
Copyright (c) 2017 Jackson Palmer

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.