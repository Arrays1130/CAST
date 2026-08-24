FROM node:22-bookworm-slim AS assets
WORKDIR /src
COPY package.json package-lock.json vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
RUN npm ci && npm run build

FROM php:8.2-cli-bookworm
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev libpq-dev libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
COPY --from=assets /src/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && mkdir -p storage/app/papers \
    && chmod +x docker/start.sh

ENV PORT=8000
EXPOSE 8000
CMD ["sh", "docker/start.sh"]
