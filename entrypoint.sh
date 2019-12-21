#!/bin/bash
set -eo pipefail

exec php /slowloris/slowloris.php $@
