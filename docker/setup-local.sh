#!/bin/sh
set -eu

until [ -f /var/www/html/wp-config.php ]; do sleep 2; done
php /tmp/install-local.php
php /tmp/seed-products.php
