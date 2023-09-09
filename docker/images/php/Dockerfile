FROM php:8.2-fpm-alpine3.18

ARG API_KEY
ARG APP_ENV
ARG APP_DEBUG
ARG APP_SECRET
ARG DATABASE_USERNAME
ARG DATABASE_PASSWORD
ARG DATABASE_HOST
ARG DATABASE_PORT
ARG DATABASE_NAME
ARG MAILER_DSN
ARG WWW_DATA_UID
ARG WWW_DATA_GID
ARG ENABLE_XDEBUG

ENV API_KEY="$API_KEY"
ENV APP_ENV="$APP_ENV"
ENV APP_DEBUG="$APP_DEBUG"
ENV APP_SECRET="$APP_SECRET"
ENV DATABASE_USERNAME="$DATABASE_USERNAME"
ENV DATABASE_PASSWORD="$DATABASE_PASSWORD"
ENV DATABASE_HOST="$DATABASE_HOST"
ENV DATABASE_PORT="$DATABASE_PORT"
ENV DATABASE_NAME="$DATABASE_NAME"
ENV MAILER_DSN="$MAILER_DSN"
ENV BUNDLE_APP_CONFIG='/usr/local/bundle'
ENV BUNDLE_SILENCE_ROOT_WARNING=1
ENV GEM_HOME="$BUNDLE_APP_CONFIG"
ENV PATH="$GEM_HOME/bin:$PATH"

RUN apk update

RUN apk add --no-cache autoconf build-base icu icu-dev

RUN apk add --no-cache freetype libjpeg-turbo libpng libxpm libwebp freetype-dev libjpeg-turbo-dev libpng-dev libxpm-dev libwebp-dev ; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-xpm --with-webp ; \
    docker-php-ext-install -j "$(getconf _NPROCESSORS_ONLN)" gd

RUN docker-php-ext-configure intl ; \
    docker-php-ext-configure pdo_mysql ; \
    docker-php-ext-install -j "$(getconf _NPROCESSORS_ONLN)" intl ; \
    docker-php-ext-install -j "$(getconf _NPROCESSORS_ONLN)" pdo_mysql

RUN if [ "$APP_ENV" = 'prod' ] ; then \
        pecl install apcu-5.1.22 ; \
        pecl install igbinary-3.2.14 ; \
        docker-php-ext-enable apcu ; \
        docker-php-ext-enable igbinary ; \
        docker-php-ext-configure opcache ; \
        docker-php-ext-install -j "$(getconf _NPROCESSORS_ONLN)" opcache ; \
    fi

RUN if [ "$ENABLE_XDEBUG" -eq 1 ] ; then \
        apk add --no-cache linux-headers ; \
        pecl install xdebug-3.2.1 ; \
        docker-php-ext-enable xdebug ; \
    fi

RUN if [ "$APP_ENV" != 'prod' ] ; then \
        apk add --no-cache git nodejs npm ruby ruby-dev ruby-etc ; \
        gem install bundle ; \
        chmod -R 0777 "$GEM_HOME" ; \
    fi

COPY --from=composer:2.5 /usr/bin/composer /usr/local/bin/composer

COPY docker /var/www/html/docker

RUN if [ "$APP_ENV" = 'prod' ] ; then \
        cp docker/images/php/php.ini-production /usr/local/etc/php/php.ini ; \
        cp docker/images/php/docker-php-ext-apcu.ini /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini ; \
        cp docker/images/php/docker-php-ext-igbinary.ini /usr/local/etc/php/conf.d/docker-php-ext-igbinary.ini ; \
        cp docker/images/php/docker-php-ext-opcache.ini /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini ; \
    else \
        cp docker/images/php/php.ini /usr/local/etc/php/php.ini ; \
    fi

RUN cp docker/images/php/php-fpm.conf /usr/local/etc/php-fpm.conf ; \
    cp docker/images/php/www.conf /usr/local/etc/php-fpm.d/www.conf ; \
    cp docker/images/php/docker-php-ext-gd.ini /usr/local/etc/php/conf.d/docker-php-ext-gd.ini ; \
    cp docker/images/php/docker-php-ext-intl.ini /usr/local/etc/php/conf.d/docker-php-ext-intl.ini ; \
    cp docker/images/php/docker-php-ext-pdo_mysql.ini /usr/local/etc/php/conf.d/docker-php-ext-pdo_mysql.ini ; \
    cp docker/images/php/docker-php-ext-sodium.ini /usr/local/etc/php/conf.d/docker-php-ext-sodium.ini

RUN if [ "$ENABLE_XDEBUG" -eq 1 ] ; then \
        cp docker/images/php/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
    fi

COPY docker/images/php/set_user /usr/local/bin/set_user

RUN set_user "$WWW_DATA_UID" "$WWW_DATA_GID"

COPY . /var/www/html

USER www-data

WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
