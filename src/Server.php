<?php

namespace RonAppleton\StaticWeb;

/**
 * Class Server
 *
 * @package RonAppleton\StaticWeb
 */
class Server
{
    /**
     * @param int $port
     * @param string $domain
     * @param string $docRoot
     * @param null $routerRoot
     */
    public static function run($port = 8000, $domain = 'localhost', $docRoot = __DIR__ . '/docroot/index.php', $routerRoot = null)
    {
        $command = "php -S $domain:$port " . $docRoot;

        if ($routerRoot) {
            $command .= ' ' . $routerRoot;
        }

        shell_exec($command . ($routerRoot ? " $routerRoot" : ''));
    }

    /**
     * Kill the server and stop resolving routes.
     */
    public static function stop()
    {
        shell_exec("killall -9 php");
    }
}
