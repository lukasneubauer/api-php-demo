# API

The goal of this project is to provide api application.

## Development

Development can be done either by using local tools or by utilizing Docker.

### Local

The following is description of how to set up and develop the project locally.

For local development, the following tools are required:

- php (8.2+)
- composer (2.4+)
- nodejs (latest)
- npm (latest)
- mysql (8.0+)

To configure the environment for local development, it is required to set up some environment variables in the `.env.local` file.

If the file does not exist, it has to be created first.

All default environment variables are stored in the `.env` file.

Not every environment variable in the `.env` file is necessary for local development.

Many of them are there for Docker setup.

The important environment variables for local development are these:

- `APP_ENV`: possible values are `dev`, `prod` and `test`
- `APP_DEBUG`: possible values are `0` and `1`
- `DATABASE_USERNAME`: set the database username for the local mysql installation
- `DATABASE_PASSWORD`: set the database password for the local mysql installation
- `DATABASE_HOST`: set the host on which the local mysql installation runs (most likely `127.0.0.1` or `localhost`)
- `DATABASE_PORT`: set the port on which the local mysql installation runs (most likely `3306`)
- `DATABASE_NAME`: set the name of the database in which the data will be stored

After the configuration is done it is required to install dependencies and initialize everything.

To install Composer packages, run:

```
composer install
```

To install NPM packages, run:

```
npm ci
```

To initialize everything, e.g. create database and load database migrations, run:

```
make lc-init
```

To initialize everything and apply database fixtures, run:

```
make lc-init-full
```

After installation of dependencies and initialization, it is necessary to start the PHP built-in web server and expose the api to the local environment.

To execute the PHP built-in web server, run:

```
bin/serve
```

Then check the output of said script and look for the api url in it. The example output could be:

```
[Sat Jan 01 12:00:00 2022] PHP 8.2.0 Development Server (http://127.0.0.1:8080) started
```

So the url to look for here is http://127.0.0.1:8080. It has to be used as the base for each api endpoint.

### Docker

The following is description of how to set up and develop the project using Docker.

If there is Docker installed on the workstation, it can be used to avoid installing all the necessary tools locally.

To configure the environment and initialize everything, e.g. install dependencies, create database and load database migrations, run:

```
make dc-init
```

To configure the environment, initialize everything and apply database fixtures, run:

```
make dc-init-full
```

Both commands will ask several questions according to which they will configure the environment:

- answer `1`, `Y` and `Y` to configure the environment for development
- answer `3`, `N` and `N` to configure the environment for production
- answer `5`, `Y` and `N` to configure the environment for testing

After the initial configuration is done, wait for the process to finish the setup.

The api is served on:

- ip: `127.0.0.1`
- port: `8080`

So use the http://127.0.0.1:8080 as the base for each api endpoint.

Finally, to stop Docker containers, run:

```
make dc-down
```

## Users In Development Environment

| Email                | Password | Account           |
|----------------------|----------|-------------------|
| john.doe@example.com | secret   | Teacher           |
| jane.doe@example.com | secret   | Student           |
| jake.doe@example.com | secret   | Teacher & Student |

## Testing

Testing can be done either by using local tools or by utilizing Docker.

### Local

To configure the environment for testing, it is required to set up some environment variables in the `.env.local` file.

The most important environment variable in question is:

- `APP_ENV`: which should be set to `test`

Afterward it is possible to run tests of two types, which are the PHPUnit and Dredd tests.

#### PHPUnit

To initialize the environment to run PHPUnit tests, run:

```
make lc-init-full-for-phpunit
```

To execute PHPUnit tests, run:

```
bin/phpunit
```

#### Dredd

To initialize the environment to run Dredd tests, run:

```
make lc-init-full-for-dredd
```

To be able to execute Dredd tests locally, it is necessary to execute the PHP built-in web server first.

To execute the PHP built-in web server, run:

```
bin/serve
```

Local Dredd tests are configured to run on http://127.0.0.1:8080, so make sure that the required port is available and not used by any other application other than the PHP built-in web server.

To execute Dredd tests, run:

```
bin/dredd
```

### Docker

To configure the environment for testing, it is required to set up the Docker environment accordingly.

Bellow is the description of how to set up and run tests of two types, which are the PHPUnit and Dredd tests.

#### PHPUnit

To initialize the environment to run PHPUnit tests, run:

```
make dc-init-full-for-phpunit
```

The command will ask several questions according to which it will configure the environment:

- answer `5`, `Y` and `N` to configure the environment for testing

To execute PHPUnit tests, run:

```
docker_bin/phpunit
```

#### Dredd

To initialize the environment to run Dredd tests, run:

```
make dc-init-full-for-dredd
```

The command will ask several questions according to which it will configure the environment:

- answer `5`, `Y` and `N` to configure the environment for testing

To execute Dredd tests, run:

```
docker_bin/dredd
```

## API Blueprint

API documentation is stored inside so-called API Blueprint, which resides in the `apiary` directory. The main files there are:

- `api-description.apib`: the source of the documentation
- `api-description.html`: the presentable version of the documentation

Each time the documentation is modified, it has to be compiled, which can be done either by using local tools or by utilizing Docker.

### Local

The following is description of how to compile the documentation locally.

For local compilation, the following tools are required:

- ruby (2.3+)
- bundler (2.1+)

To install Bundler packages, run:

```
bundle install
```

Afterward it is possible to compile the documentation by running:

```
bin/apidoc
```

### Docker

To have all the necessary tools present in the Docker container, it is required to configure the environment either for development or for testing.

Keep in mind, that production setup does not include these tools.

Set up the Docker environment using one of the following two options, when running either:

```
make dc-init
```

or

```
make dc-init-full
```

- answer `1`, `Y` and `Y` to configure the environment for development
- answer `5`, `Y` and `N` to configure the environment for testing

Afterward it is possible to compile the documentation by running:

```
docker_bin/apidoc
```

## Postman

The Postman resource files reside in the `postman` directory. These files are:

- `env.json`: the Postman environment
- `api.json`: the Postman requests collection

Both of these files can be imported into Postman and can be used for development and testing purposes.

Each request in the collection is set with a `Cookie` header, which enables debugging inside IDE.
