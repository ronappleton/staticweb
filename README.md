# Static Web

## Overview

Static Web is a real simple library for simulating webserver responses.
It was created for the purpose of testing the middleware of a guzzle client.

It was created for using in unit tests.

Its usage is simple, it's operation is simple, because of this, any version of php above 5.4 can be used.

## Installation

    composer require ronappleton/static-web

## Usage

This usage example covers unit tests only as that it was made for, however it is not limited
to that usage, but please only use it for local testing environments, it's single
threaded and thus will block and is in no way useable in a production environment.

Add 2 use statements to the top of you test class

```php
use RonAppleton\StaticWeb\Server;
use RonAppleton\StaticWeb\Router;
```

In the constructor of your test class or set up method, create a new router instance to use a global router using `$this->router->clearRoutes()`
at the start of your test methods.

A thing to note is that the routes are reloaded before every request is processed

```php
Server::run($port, $domain, $docRoot, $routerRoute);
$this->router = new Router();
```

The server port is an int with the remaining fields strings.

To add routes, use the following syntax

```php
$router->get($route, $content, $options = []);
```

Where `route` is a string, `content` is a string, either a normal string or a php script to be evaluated and `$options` 
is an array of optional details for your requests like headers, or response details.

All standard route methods are available

 - GET
 - HEAD
 - POST
 - PUT
 - DELETE
 - CONNECT
 - OPTIONS
 - TRACE
 - PATCH

However, you should continually bear in mind that it is up to your to provide the responses.

In your tear down method simply call `Server::stop();` to kill the server.

Whilst the server is limited in its usage, remember that you can pass the contents of a php file to be evaulated before
returning the response, this means that all your super globals etcetera are available for your usage so if you need query parameters,
get them within your evaluated script.
