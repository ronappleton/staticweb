<?php

namespace RonAppleton\StaticWeb;

/**
 * Class Router
 *
 * @package RonAppleton\StaticWeb
 */
class Router
{
    /**
     * @var array
     */
    protected $routeCache = [];

    /**
     * @var string
     */
    protected $resolving = null;

    public function __construct()
    {
        if (!defined('STDOUT')) {
            define('STDOUT', fopen('php://stdout', 'w'));
        }

        if (!defined('STDERR')) {
            define('STDERR', fopen('php://stderr', 'w'));
        }
    }

    /**
     * @param string $route
     * @param string $content
     * @param array $options
     */
    public function get($route, $content, $options = [])
    {
        $this->addRoute('GET', $route, $content, $options);
    }

    /**
     * @param string $route
     * @param string $content
     * @param array $options
     */
    public function head($route, $content, $options = [])
    {
        $this->addRoute('HEAD', $route, $content, $options);
    }

    /**
     * @param string $route
     * @param string $content
     * @param array $options
     */
    public function post($route, $content, $options = [])
    {
        $this->addRoute('POST', $route, $content, $options);
    }

    /**
     * @param string $route
     * @param string $content
     * @param array $options
     */
    public function put($route, $content, $options = [])
    {
        $this->addRoute('PUT', $route, $content, $options);
    }

    /**
     * @param string $route
     * @param string $content
     * @param array $options
     */
    public function delete($route, $content, $options = [])
    {
        $this->addRoute('DELETE', $route, $content, $options);
    }

    /**
     * @param string $route
     * @param string $content
     * @param array $options
     */
    public function connect($route, $content, $options = [])
    {
        $this->addRoute('CONNECT', $route, $content, $options);
    }

    /**
     * @param string $route
     * @param string $content
     * @param array $options
     */
    public function options($route, $content, $options = [])
    {
        $this->addRoute('OPTIONS', $route, $content, $options);
    }

    /**
     * @param string $route
     * @param string $content
     * @param array $options
     */
    public function trace($route, $content, $options = [])
    {
        $this->addRoute('TRACE', $route, $content, $options);
    }

    /**
     * @param string $route
     * @param string $content
     * @param array $options
     */
    public function patch($route, $content, $options = [])
    {
        $this->addRoute('PATCH', $route, $content, $options);
    }

    /**
     * @param string $route
     * @param string $content
     * @param array $options
     */
    public function any($route, $content, $options = [])
    {
        $this->addRoute('ANY', $route, $content, $options);
    }

    /**
     * Add our route to the route cache.
     *
     * @param string|array $method
     * @param string $route
     * @param string $content
     * @param array $options
     */
    protected function addRoute($method, $route, $content, $options = [])
    {
        $route = ltrim($route, '/');

        $this->routeCache[$route]['methods'] = (array)$method;
        $this->routeCache[$route]['options'] = (array)$options;
        $this->routeCache[$route]['content'] = $content;

        $this->addToCache();
    }

    /**
     * Handle the requested route.
     */
    public function resolve()
    {
        $this->loadCache();
        $this->setResolving();

        if (!isset($this->routeCache[$this->resolving]) || null === $this->routeCache) {
            error_log("Route [$this->resolving] not found, sending 404");
            http_response_code(404);
            header("HTTP/1.0 404 Not Found", true);
            exit;
        }

        $this->validateMethod();
        $this->setHeaders();

        $content = $this->getContent();
        $content = strpos($content, '<?php') !== false ? eval(str_replace('<?php', '', $content) . PHP_EOL) : $content;
        echo $content;

        fwrite(STDOUT, '[' . date('Y-m-d H:i:s') . ']' . " - Route [$this->resolving] successfully processed" . PHP_EOL);
        fwrite(STDOUT, $content . PHP_EOL);
        exit;
    }

    /**
     * Load our routes from the route cache.
     */
    protected function loadCache()
    {
        if (file_exists(__DIR__ . '/routes/route_cache.php')) {
            $this->routeCache = include __DIR__ . '/routes/route_cache.php';
        }
    }

    /**
     * Store our routes to cache.
     */
    protected function addToCache()
    {
        file_put_contents(
            __DIR__ . '/routes/route_cache.php',
            '<?php return ' . var_export($this->routeCache, true) . ';' . PHP_EOL
        );
    }

    /**
     * Set the route name we are resolving.
     */
    protected function setResolving()
    {
        $this->resolving = ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    }

    /**
     * Get the array of route components.
     *
     * @return array|null
     */
    protected function getRoute()
    {
        return isset($this->routeCache[$this->resolving]) ? $this->routeCache[$this->resolving] : null;
    }

    /**
     * Get the route response headers.
     *
     * @return array|null
     */
    protected function getHeaders()
    {
        return isset($this->routeCache[$this->resolving]['options']['headers']) ? $this->routeCache[$this->resolving]['options']['headers'] : null;
    }

    /**
     * Get the route content.
     *
     * @return string
     */
    protected function getContent()
    {
        return isset($this->routeCache[$this->resolving]['content']) ? $this->routeCache[$this->resolving]['content'] : '';
    }

    /**
     * If we have a pre-defined response, use that.
     *
     * @return array|null
     */
    protected function getResponse()
    {
        return isset($this->routeCache[$this->resolving]['options']['response']) ? $this->routeCache[$this->resolving]['options']['response'] : null;
    }

    /**
     * Get the acceptable route methods.
     *
     * @return array|null
     */
    protected function getMethods()
    {
        return isset($this->routeCache[$this->resolving]['methods']) ? $this->routeCache[$this->resolving]['methods'] : null;
    }

    /**
     * Validate the request method used to call the route.
     */
    protected function validateMethod()
    {
        $methods = $this->getMethods();
        $requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);

        if (!in_array($requestMethod, $methods, true) && !in_array('ANY', $methods, true)) {
            error_log("Route [$this->resolving] called with wrong method [$requestMethod]");
            header($_SERVER["SERVER_PROTOCOL"] . " 405 Method Not Allowed", true, 405);
            exit;
        }
    }

    /**
     * Set the response headers that are appropriate.
     */
    protected function setHeaders()
    {
        $headers = $this->getHeaders();
        $response = $this->getResponse();

        http_response_code(200);

        if (null !== $response) {
            $message = $_SERVER["SERVER_PROTOCOL"];
            if (is_array($response)) {
                if (isset($reponse['code'])) {
                    http_response_code($response['code']);
                    $message .= ' ' . $response['code'];
                }
                if (isset($response['message'])) {
                    $message .= !isset($response['code']) ? ' ' : '';
                    $message .= $response['message'];
                }
            }

            header(
                rtrim($message, ' '),
                true,
                isset($response['code']) ? (int)$response['code'] : http_response_code()
            );
        }

        if (null !== $headers) {
            foreach ($headers as $headerKey => $headerValue) {
                $headerKey = rtrim($headerKey, ':');
                header("{$headerKey}:{$headerValue}", true);
            }
        }
    }

    /**
     * Clear all stored routes.
     */
    public function clearRoutes()
    {
        $this->routeCache = [];
        $this->addToCache();
    }
}
