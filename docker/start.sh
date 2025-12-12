#!/usr/bin/env bash
set -e

role=${CONTAINER_ROLE:-app}
env=${APP_ENV:-production}

# App
if [ "$role" = "app" ]; then

    exec php-fpm

# Queue
elif [ "$role" = "console" ]; then

    ln -sf /etc/supervisor/conf.d-available/queue.conf /etc/supervisor/queue.conf
    exec supervisord -c /etc/supervisor/supervisord.conf

else
    echo "Could not match the container role \"$role\""
    exit 1
fi

