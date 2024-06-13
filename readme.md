# Livety-api

## Descargar Repositorio

Descargar el repositorio desde GitHub descargado el zip o clonando el repo:

```bash
git clone https://github.com/carladev/livety-api.git
cd liberty-api
```

## Producción

### Requisitos Previos

- Docker: [Instalar Docker](https://docs.docker.com/get-docker/)
- Docker Compose: [Instalar Docker Compose](https://docs.docker.com/compose/install/)

### Levantar contenedor de Docker

```bash
docker-compose up -d
```

## Desarollo (solo usar para desarollo)

Levantar imagen de mysql en docker

```bash
docker pull mysql
docker run --name mysql-test-8 -e MYSQL_ROOT_PASSWORD=pass -p3306:3306 -d mysql:latest
```

```bash
php -S localhost:8888 -t public
```
