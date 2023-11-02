FROM node:16.20-alpine as build
LABEL maintainer="demorfi@gmail.com"
WORKDIR /usr/src/app

COPY package.json vite.config.js ./
COPY ./resource ./resource

RUN npm install
RUN npm run build

FROM webdevops/php-nginx:8.2-alpine as production
LABEL maintainer="demorfi@gmail.com"
WORKDIR /synox-web

ENV TZ=Europe/Moscow
ENV APP_MODE=prod

COPY --chown=application:application . ./
COPY --from=build /usr/src/app/public/assets ./public/assets

RUN rm -rf /app
RUN ln -s /synox-web/public /app
RUN echo "chown -R application:application /synox-web" > /opt/docker/provision/entrypoint.d/20-synox-web.sh
RUN composer install --optimize-autoloader --no-interaction --no-progress
EXPOSE 80
EXPOSE 2346
VOLUME ["/synox-web/storage", "/synox-web/public/files"]
