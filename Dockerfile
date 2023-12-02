FROM node:21.1-alpine as build
WORKDIR /usr/src/app

COPY package.json vite.config.js ./
COPY ./resource ./resource

RUN npm install
RUN npm run build

FROM webdevops/php-nginx:8.2-alpine as production
WORKDIR /synox-web

ARG TZ=Europe/Moscow
ARG APP_MODE=prod
ARG REDIS_HOST=localhost
ARG REDIS_PORT=6379
ARG REDIS_PASSWORD=secret
ARG CACHE_EXPIRE=86400

ENV TZ=${TZ}
ENV APP_MODE=${APP_MODE}
ENV REDIS_HOST=${REDIS_HOST}
ENV REDIS_PORT=${REDIS_PORT}
ENV REDIS_PASSWORD=${REDIS_PASSWORD}
ENV CACHE_EXPIRE=${CACHE_EXPIRE}

COPY --chown=application:application . ./
COPY --from=build /usr/src/app/public/assets ./public/assets

RUN rm -rf /app
RUN ln -s /synox-web/public /app
RUN echo "chown -R application:application /synox-web" > /opt/docker/provision/entrypoint.d/20-synox-web.sh
RUN composer install --optimize-autoloader --no-interaction --no-progress
EXPOSE 80 2346
VOLUME ["/synox-web/storage", "/synox-web/public/files"]