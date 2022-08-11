#!/bin/sh
composer dump-autoload --optimize && \
composer check-platform-reqs && \
composer run-script post-install-cmd && \
php bin/console cache:warmup && \
rr serve -c .rr.dev.yaml