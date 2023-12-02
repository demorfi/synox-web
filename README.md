# SynoX Web

Search engine based on modular architecture. You can search and download torrent files, text files
or any other information within the packages presented. Create your own packages yourself or
use ready-made ones.

## Features
* [Digua](https://github.com/demorfi/digua) PHP Micro Framework
* Performing a multi-threaded search.
* Download or view the content of a search result.
* Landing page
* Support for additional extensions

## [Docker](http://docker.io) Setup
This application is also available as a docker container.
You can run this application using the following command:

```bash
docker run -d --name synox-web \
  --restart="always" \
  -p 8002:80/tcp \
  -p 2346:2346/tcp \
  -v /synox-web/storage:/synox-web/storage \
  -v /synox-web/files:/synox-web/public/files \
  demorfi/synox-web:latest
```
This will make the application available on port 8002 on the docker host.

## [Docker](http://docker.io) Build
You can build and run a docker container locally using:
```bash
docker build -t synox-web:latest . && \
docker run -d --name synox-web \
  --restart="always" \
  -p 8002:80/tcp \
  -p 2346:2346/tcp \
  -v /synox-web/storage:/synox-web/storage \
  -v /synox-web/files:/synox-web/public/files \
  synox-web:latest
```
or docker compose (*Redis included!)
```bash
docker-compose up -d
```

## Manual Setup
### Requirements
* Nginx/Apache/Built-in Web Server
* PHP 8.2 or greater
* Node & NPM
* Redis (*optional)

### Installation
* Clone the repository to your webserver and set root mount point for you webserver to `/public` directory
* Run composer install `composer install`
* Run npm install `npm install`
* Build npm `npm run build`
* [* Use .env.example or /config files to set your personal preferences]

## Reporting issues
If you have any issues with with the application please open an issue on [GitHub](https://github.com/demorfi/synox-web/issues).

License
=======
SynoX Web is licensed under the [MIT License](http://www.opensource.org/licenses/mit-license.php).