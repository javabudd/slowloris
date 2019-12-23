<?php

include __DIR__ . '/functions.php.inc';

[$hostname, $port, $socketCount, $timeout] = validateArguments($argv);
$listOfSockets                             = [];

registerSignals($listOfSockets);

loop($hostname, $port, $socketCount, $timeout, $listOfSockets);
