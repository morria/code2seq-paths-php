FROM php:8.2.2-cli

RUN apt-get update --fix-missing

# nikic/php-ast
RUN apt-get install -y git
RUN cd /tmp; git clone https://github.com/nikic/php-ast.git && cd php-ast && phpize && ./configure && make && make install
RUN echo "extension=ast" > /usr/local/etc/php/conf.d/docker-php-ast.ini
RUN rm -rf /tmp/php-ast
RUN apt-get purge -y git

# Composer
RUN cd /tmp; curl -O https://getcomposer.org/installer && php ./installer --install-dir=/usr/local/bin --filename=composer
RUN rm /tmp/installer

RUN mkdir -p /code2seq-paths-php
WORKDIR /code2seq-paths-php

# Copy the source and executable
COPY vendor /code2seq-paths-php/vendor
COPY src /code2seq-paths-php/src
COPY bin /code2seq-paths-php/bin

ENTRYPOINT ["/code2seq-paths-php/bin/code2seq-paths"]