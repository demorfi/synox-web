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
ARG WORKER_BROADCAST_HOST=0.0.0.0
ARG WORKER_BROADCAST_PORT=2346
ARG WORKER_USE_SSL=false
ARG WORKER_SSL_CERT_PATH=null
ARG WORKER_SSL_KEY_PATH=null

ENV TZ=${TZ}
ENV APP_MODE=${APP_MODE}
ENV REDIS_HOST=${REDIS_HOST}
ENV REDIS_PORT=${REDIS_PORT}
ENV REDIS_PASSWORD=${REDIS_PASSWORD}
ENV CACHE_EXPIRE=${CACHE_EXPIRE}
ENV WORKER_BROADCAST_HOST=${WORKER_BROADCAST_HOST}
ENV WORKER_BROADCAST_PORT=${WORKER_BROADCAST_PORT}
ENV WORKER_USE_SSL=${WORKER_USE_SSL}
ENV WORKER_SSL_CERT_PATH=${WORKER_SSL_CERT_PATH}
ENV WORKER_SSL_KEY_PATH=${WORKER_SSL_KEY_PATH}

COPY --chown=application:application . ./
COPY --from=build /usr/src/app/public/assets ./public/assets

RUN rm -rf /app
RUN ln -s /synox-web/public /app
RUN echo "chown -R application:application /synox-web" > /opt/docker/provision/entrypoint.d/20-synox-web.sh
RUN composer install --optimize-autoloader --no-interaction --no-progress
EXPOSE 80 2346
VOLUME ["/synox-web/storage", "/synox-web/public/files"]