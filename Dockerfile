FROM ubirak/php-docker:7.2.0-stable as latest

USER php

ENV PATH="$PATH:/home/php/app/bin" \
    APP_ENV="prod" \
    APP_DEBUG="0"

COPY --chown=php:php composer.json composer.lock symfony.lock ./
RUN composer install --ansi --no-autoloader --no-dev --no-scripts \
    && composer clear-cache --ansi

COPY --chown=php:php config ./config
COPY --chown=php:php bin ./bin
COPY --chown=php:php src ./src

RUN composer dump-autoload --ansi --no-dev --classmap-authoritative

ENTRYPOINT ["console"]

FROM latest as dev

ENV APP_ENV="dev" \
    APP_DEBUG="1"

RUN composer install --ansi --no-autoloader \
    && composer clear-cache --ansi

COPY --chown=php:php .*atoum.php .php_cs* ./
COPY --chown=php:php tests ./tests

RUN composer dump-autoload --ansi

ENTRYPOINT ["composer"]