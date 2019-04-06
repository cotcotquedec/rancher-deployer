FROM debian:stretch-slim

LABEL maintainer="Julien H. <julien.houvion@gmail.com>"

# Install base package
RUN apt-get update && apt-get install -y --no-install-recommends \
        apt-transport-https \
        ca-certificates \
        curl \
        fonts-arphic-* \
        git \
        gosu \
        libcap2-bin \
        locales \
        lsb-release \
        supervisor \
        wget \
# Add php keys
    && wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
    && echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" >> /etc/apt/sources.list \
# Install php and tools
    && apt-get update && apt-get install -y --no-install-recommends \
        nano \
        php7.3-cli \
        php7.3-curl \
        php7.3-imap \
        php7.3-intl \
        php7.3-mbstring \
        php7.3-mysqlnd \
        php7.3-soap \
        php7.3-xml \
        php7.3-zip \
# Clean cache
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /var/cache/debconf


#COMPOSER
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /tmp
ENV COMPOSER_VERSION 1.8.4

RUN curl --silent --fail --location --retry 3 --output /tmp/installer.php --url https://raw.githubusercontent.com/composer/getcomposer.org/cb19f2aa3aeaa2006c0cd69a7ef011eb31463067/web/installer \
 && php -r " \
    \$signature = '48e3236262b34d30969dca3c37281b3b4bbe3221bda826ac6a9a62d6444cdb0dcd0615698a5cbe587c3f0fe57a54d8f5'; \
    \$hash = hash('sha384', file_get_contents('/tmp/installer.php')); \
    if (!hash_equals(\$signature, \$hash)) { \
        unlink('/tmp/installer.php'); \
        echo 'Integrity check failed, installer is either corrupt or worse.' . PHP_EOL; \
        exit(1); \
    }" \
 && php /tmp/installer.php --no-ansi --install-dir=/usr/bin --filename=composer --version=${COMPOSER_VERSION} \
 && composer --ansi --version --no-interaction \
 && rm -f /tmp/installer.php

# COPY SCRIPT
RUN mkdir /app
WORKDIR /app
COPY . .

# COMPOSER
RUN rm -Rf vendor composer.lock \
 && composer install --no-dev --no-suggest -o -n --no-progress --profile --prefer-dist

ENTRYPOINT ["php", "deploy.php"]