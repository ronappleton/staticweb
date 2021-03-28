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
     * @param string|null $outputPath
     * @return false|resource
     */
    public static function run($port = 8000, $domain = 'localhost', $docRoot = __DIR__ . '/docroot/index.php', $routerRoot = null, $outputPath = '/dev/null 2>&1;')
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['file', $outputPath . 'log.txt', 'w'],
            2 => ['file', $outputPath . 'errors.txt', 'w'],
        ];

        $command = "php -S $domain:$port " . $docRoot;

        if ($routerRoot) {
            $command .= ' ' . $routerRoot;
        }

        return proc_open($command, $descriptors, $pipes);
    }

    /**
     * Kill the server and stop resolving routes.
     * @param $process
     */
    public static function stop($process)
    {
        echo proc_terminate($process);
    }
}
