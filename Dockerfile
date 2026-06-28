FROM node:22-bookworm-slim AS node
FROM composer:2 AS composer-bin

FROM php:8.4-cli-bookworm AS app

ARG UID=1000
ARG GID=1000

ENV COMPOSER_HOME=/tmp/composer \
    NPM_CONFIG_CACHE=/tmp/npm-cache \
    PHP_MEMORY_LIMIT=256M \
    PHP_UPLOAD_MAX_FILESIZE=16M \
    PHP_POST_MAX_SIZE=16M

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        default-mysql-client \
        dumb-init \
        git \
        unzip \
    && docker-php-ext-install pdo_mysql \
    && groupadd --gid "${GID}" app \
    && useradd --uid "${UID}" --gid app --shell /bin/bash --create-home app \
    && mkdir -p /tmp/composer /tmp/npm-cache /var/www/html \
    && chown -R app:app /tmp/composer /tmp/npm-cache /var/www/html \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer-bin /usr/bin/composer /usr/bin/composer
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -sf /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -sf /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

COPY docker/php.ini /usr/local/etc/php/conf.d/99-betini.ini
COPY docker/entrypoint.sh /usr/local/bin/betini-entrypoint
RUN chmod +x /usr/local/bin/betini-entrypoint

USER app

EXPOSE 8000

ENTRYPOINT ["dumb-init", "--", "betini-entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
