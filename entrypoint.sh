#!/bin/bash
set -eo pipefail

cd /slowloris

php /composer.phar install -o

exec php slowloris.php $@
