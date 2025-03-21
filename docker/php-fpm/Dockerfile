ARG PHP_VERSION

FROM php:${PHP_VERSION}

LABEL maintainer = "Victor Hugo <VictorH.G.Avila@hotmail.com>"

ENV EXT_AMQP_VERSION=lasted

ARG UID

RUN adduser --disabled-password --gecos '' appuser

RUN mkdir -p /home/appuser/.ssh && chmod 777 -R /home/appuser/.ssh
COPY id_rsa /home/appuser/.ssh/id_rsa
COPY id_rsa /root/.ssh/id_rsa
RUN chmod -R 600 /home/appuser/.ssh/id_rsa /root/.ssh/id_rsa


RUN mkdir -p /home/appuser/.composer /.composer
RUN chmod 777 -R /home/appuser/.composer /.composer


RUN chown -R appuser:appuser /home/appuser/

RUN echo "StrictHostKeyChecking no" >> /root/.ssh/config
RUN echo "StrictHostKeyChecking no" >> /home/appuser/.ssh/config
RUN echo "    IdentityFile /home/appuser/.ssh/id_rsa" >> /home/appuser/.ssh/config
RUN echo "export COLUMNS=300" >> /home/appuser/.bashrc
RUN echo "alias sf=/var/www/html/bin/console" >> /home/appuser/.bashrc

# Additional tools
RUN apt-get update && apt-get install -y \
    apt-utils \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libssl-dev \
    libzip-dev \
    libonig-dev \
    libmcrypt-dev \
    libpng-dev \
    tzdata \
    wget \
    zip \
    unzip \
    git \
    vim \
    librabbitmq-dev \
    librdkafka-dev

RUN wget https://get.symfony.com/cli/installer -O - | bash

# Common php-ext and requirements
RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev libz-dev \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install session \
    && docker-php-ext-install iconv \
    && docker-php-ext-install pdo

RUN docker-php-ext-install sockets

#####################################
# SET TIMEZONE:
#####################################

ARG TIMEZONE
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE}/etc/localtime && echo ${TIMEZONE} > /etc/timezone \
    && printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini \
    && "date"

#####################################
# GD:
#####################################

ARG INSTALL_GD=false
RUN if [ ${INSTALL_GD} = true ]; then \
    # Install gd and requirements
    apt-get update && apt-get install -y --no-install-recommends libjpeg-dev libpng-dev libfreetype6-dev \
    && docker-php-ext-install gd \
    ;fi

#####################################
# compression:
#####################################

ARG INSTALL_COMPRESSION=false
RUN if [ ${INSTALL_COMPRESSION} = true ]; then \
    # Install zip, bz2 and requirements
    apt-get update && apt-get install -y --no-install-recommends libbz2-dev zlib1g-dev \
    && docker-php-ext-install zip \
    bz2 \
    ;fi

#####################################
# FTP:
#####################################

ARG INSTALL_FTP=false
RUN if [ ${INSTALL_FTP} = true ]; then \
    # Install ftp and requirements
    apt-get update && apt-get install -y --no-install-recommends libssl-dev \
    && docker-php-ext-install ftp \
    ;fi

#####################################
# Mbstring:
#####################################

ARG INSTALL_MBSTRING=false
RUN if [ ${INSTALL_MBSTRING} = true ]; then \
    # Install mbstring
    docker-php-ext-install mbstring \
    ;fi

#####################################
# PDO_MYSQL:
#####################################

ARG INSTALL_PDO_MYSQL=false
RUN if [ ${INSTALL_PDO_MYSQL} = true ]; then \
    # Install pdo_mysql
    docker-php-ext-install pdo_mysql \
    ;fi

#####################################
# Mysqli Modifications:
#####################################

ARG INSTALL_MYSQLI=false
RUN if [ ${INSTALL_MYSQLI} = true ]; then \
    # Install mysqli
    docker-php-ext-install mysqli \
    ;fi

#####################################
# Mysqli Modifications:
#####################################

RUN apt update && apt install -y default-mysql-client

#####################################
# bcmath:
#####################################

ARG INSTALL_BCMATH=false
RUN if [ ${INSTALL_BCMATH} = true ]; then \
    # Install bcmath extension
    docker-php-ext-install bcmath \
    ;fi

#####################################
# Redis:
#####################################

ARG INSTALL_REDIS=false
RUN if [ ${INSTALL_REDIS} = true ]; then \
    pecl install redis && \
    docker-php-ext-enable redis \
    ;fi


#####################################
# RDKafka:
#####################################
ARG INSTALL_KAFKA=false
RUN if [ ${INSTALL_KAFKA} = true ]; then \
    apt-get install -y librdkafka-dev \
    python3 \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka \
    ;fi

#####################################
# APCu:
#####################################

ARG INSTALL_APCU=false
RUN if [ ${INSTALL_APCU} = true ]; then \
    pecl install apcu \
    && docker-php-ext-enable apcu \
    && pecl clear-cache \
    ;fi

#####################################
# Opcache:
#####################################

ARG INSTALL_OPCACHE=false
RUN if [ ${INSTALL_OPCACHE} = true ]; then \
    docker-php-ext-install opcache \
    ;fi

# Copy opcache configration
COPY ./opcache.ini /usr/local/etc/php/conf.d/opcache.ini

#####################################
# xdebug:
#####################################

ARG INSTALL_XDEBUG=false
RUN if [ ${INSTALL_XDEBUG} = true ]; then \
    # Install the xdebug
    pecl install xdebug-3.3.1 \
    && docker-php-ext-enable xdebug \
    ;fi

# Copy xdebug configration
COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

#####################################
# composer:
#####################################

ARG INSTALL_COMPOSER=false
RUN if [ ${INSTALL_COMPOSER} = true ]; then \
    # Install the composer
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    ;fi

#####################################
# tokenizer:
#####################################

ARG INSTALL_TOKENIZER=false
RUN if [ ${INSTALL_TOKENIZER} = true ]; then \
    # Install tokenizer
    docker-php-ext-install tokenizer \
    ;fi

#####################################
# json, xml, dom xmlrpc, xsl:
#####################################

RUN apt-get update \
    && apt-get install -y libxml2-dev \
    && docker-php-ext-install xml

#####################################
# Human Language and Character Encoding Support:
#####################################

ARG INSTALL_INTL=false
RUN if [ ${INSTALL_INTL} = true ]; then \
    # Install intl and requirements
    apt-get update && apt-get install -y --no-install-recommends zlib1g-dev libicu-dev g++ \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    ;fi

# Clear package lists
RUN rm -rf /var/lib/apt/lists/*

ARG INSTALL_AMQP=false
RUN if [ ${INSTALL_AMQP} = true ]; then \
    # install AMQP
    docker-php-source extract \
    && git clone --branch $EXT_AMQP_VERSION --depth 1 https://github.com/php-amqp/php-amqp.git /usr/src/php/ext/amqp \
    && cd /usr/src/php/ext/amqp && git submodule update --init \
    && docker-php-ext-install amqp \
    ;fi

COPY ./php.ini /usr/local/etc/php/php.ini
COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Permissions
RUN chown -R appuser:appuser /var/www/html
RUN chmod u+rwx,g+rx,o+rx /var/www/html

RUN find /var/www/html -type d -exec chmod u+rwx,g+rx,o+rx {} +
RUN find /var/www/html -type f -exec chmod u+rw,g+rw,o+r {} +

USER appuser

CMD ["php-fpm"]

EXPOSE 9000