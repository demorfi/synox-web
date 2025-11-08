FROM node:21.1-alpine AS build
WORKDIR /usr/src/app

COPY package.json package-lock.json vite.config.js ./
COPY ./resource ./resource

RUN npm install
RUN npm run build

FROM webdevops/php-nginx:8.2-alpine AS production
WORKDIR /synox-web

ARG TZ
ARG APP_MODE
ARG REDIS_HOST
ARG REDIS_PORT
ARG REDIS_PASSWORD
ARG CACHE_EXPIRE
ARG ELASTIC_HOST
ARG ELASTIC_API_ID
ARG ELASTIC_API_KEY
ARG ELASTIC_CLOUD_ID
ARG ELASTIC_TIMEOUT
ARG WORKER_BROADCAST_HOST
ARG WORKER_BROADCAST_PORT
ARG WORKER_USE_SSL
ARG WORKER_SSL_CERT_PATH
ARG WORKER_SSL_KEY_PATH

ENV TZ=${TZ:-Europe/Moscow}
ENV APP_MODE=${APP_MODE:-prod}
ENV REDIS_HOST=${REDIS_HOST:-localhost}
ENV REDIS_PORT=${REDIS_PORT:-6379}
ENV REDIS_PASSWORD=${REDIS_PASSWORD:-secret}
ENV CACHE_EXPIRE=${CACHE_EXPIRE:-86400}
ENV ELASTIC_HOST=${ELASTIC_HOST:-localhost:9201}
ENV ELASTIC_API_ID=${ELASTIC_API_ID:-null}
ENV ELASTIC_API_KEY=${ELASTIC_API_KEY:-null}
ENV ELASTIC_CLOUD_ID=${ELASTIC_CLOUD_ID:-null}
ENV ELASTIC_TIMEOUT=${ELASTIC_TIMEOUT:-1.5}
ENV WORKER_BROADCAST_HOST=${WORKER_BROADCAST_HOST:-0.0.0.0}
ENV WORKER_BROADCAST_PORT=${WORKER_BROADCAST_PORT:-2346}
ENV WORKER_USE_SSL=${WORKER_USE_SSL:-false}
ENV WORKER_SSL_CERT_PATH=${WORKER_SSL_CERT_PATH:-null}
ENV WORKER_SSL_KEY_PATH=${WORKER_SSL_KEY_PATH:-null}

COPY --chown=application:application . ./
COPY --from=build /usr/src/app/public/assets ./public/assets

RUN rm -rf /app
RUN ln -s /synox-web/public /app
RUN echo "mkdir -p /synox-web/storage/packages /synox-web/storage/settings /synox-web/storage/states" > /opt/docker/provision/entrypoint.d/20-synox-web.sh
RUN echo "chown -R application:application /synox-web" >> /opt/docker/provision/entrypoint.d/20-synox-web.sh
RUN composer install --optimize-autoloader --no-interaction --no-progress
EXPOSE 80 2346
VOLUME ["/synox-web/storage", "/synox-web/public/files"]