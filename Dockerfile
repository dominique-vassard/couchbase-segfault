FROM php:8-cli-buster

ENV COUCHBASE_VERSION 3.0.5

RUN apt update && apt install -y --no-install-recommends \
    wget gnupg2 

# COUCHBASE
RUN wget https://packages.couchbase.com/clients/c/repos/deb/couchbase.key \
    && apt-key add couchbase.key \
    && echo "deb https://packages.couchbase.com/clients/c/repos/deb/debian10 buster buster/main" >> /etc/apt/sources.list \
    && apt update \
    && apt install -y --no-install-recommends libcouchbase-dev libcouchbase3-tools \
    && rm -rf /var/lib/apt/lists/* \
    && pecl install https://packages.couchbase.com/clients/php/couchbase-${COUCHBASE_VERSION}.tgz 

RUN echo "extension=couchbase.so" > /usr/local/etc/php/conf.d/couchbase.ini \
    && echo "; priority=50" >> /usr/local/etc/php/conf.d/couchbase.ini \
    # && phpenmod couchbase