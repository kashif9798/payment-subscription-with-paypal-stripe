FROM php:8.1-apache

RUN apt-get update && apt-get -y install software-properties-common git unzip bc curl libxslt-dev libpcre3-dev openssl make libssl-dev libffi-dev gcc libxml2-dev apt-transport-https zlib1g-dev  libcurl4-openssl-dev  libzip-dev zip libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libedit-dev && \
    a2enmod rewrite && \
    a2enmod status && \
    a2enmod expires && \
    a2enmod ssl && \
    a2enmod proxy && \
    a2enmod proxy_http && \
    curl --silent https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer 

RUN apt-get install -y libyaml-dev && \
    pecl install yaml-2.2.2

RUN echo "extension=yaml.so" > $PHP_INI_DIR/conf.d/20-yaml.ini

RUN docker-php-ext-install zip gd sockets
RUN docker-php-ext-configure intl && docker-php-ext-install intl;

RUN apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql pcntl


RUN apt-get install -y build-essential gcc g++ make

RUN pecl install redis && docker-php-ext-enable redis

ARG ENABLE_XDEBUG
RUN test ! -z $ENABLE_XDEBUG && (pecl install xdebug && docker-php-ext-enable xdebug) || true

RUN \
    a2enmod rewrite && \
    a2enmod status && \
    a2enmod expires && \
    a2enmod proxy && \
    a2enmod proxy_http && \
    rm /etc/apache2/sites-enabled/00*
RUN \
    apt-get remove -y bc libxslt-dev libpcre3-dev libssl-dev
RUN \
    apt-get remove -y libffi-dev gcc libxml2-dev apt-transport-https zlib1g-dev  libcurl4-openssl-dev  libfreetype6-dev
RUN \
    apt-get remove -y \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libedit-dev && \
    adduser --disabled-password --gecos "" search && \
    mkdir /var/log/search && \
    chown search.search /var/log/search && \
    rm -rf /var/lib/apt/lists/*

COPY config/sites-enabled/payment-integration.conf /etc/apache2/sites-enabled/
COPY config/mods-enabled/status.conf /etc/apache2/mods-enabled/status.conf
COPY config/apache2.conf /etc/apache2/
COPY config/ports.conf /etc/apache2/

# ENV NVM_DIR="/root/.nvm"
# ENV NODE_VERSION="18.1.0"
# ENV MAIN_DIR="$NVM_DIR//versions/node/v$NODE_VERSION"

# RUN curl https://raw.githubusercontent.com/creationix/nvm/v0.35.0/install.sh | bash
# RUN . $NVM_DIR/nvm.sh && nvm install $NODE_VERSION && nvm alias default $NODE_VERSION && nvm use default

# RUN ln -s $MAIN_DIR/bin/node /usr/bin/node
# RUN ln -s $MAIN_DIR/bin/npm /usr/bin/npm

# ENV NODE_PATH = $MAIN_DIR/lib/node_modules
# ENV PATH = $MAIN_DIR/bin:$PATH

# RUN npm set unsafe-perm true
# RUN npm install --global yarn
# RUN $MAIN_DIR/bin/yarn install
# RUN $MAIN_DIR/bin/yarn global add vuepress

WORKDIR /home/payment-integration