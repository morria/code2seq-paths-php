FROM php:8.2.2-cli

RUN apt-get update --fix-missing
RUN apt-get install -y software-properties-common
RUN apt-get install -y git curl vim bash sudo tmux man ack zip unzip

# nikic/php-ast
RUN cd /tmp; git clone https://github.com/nikic/php-ast.git && cd php-ast && phpize && ./configure && make && make install
RUN echo "extension=ast" > /usr/local/etc/php/conf.d/php-ast.ini

# xdebug
RUN cd /tmp; curl -O https://xdebug.org/files/xdebug-3.2.1.tgz && tar -xvzf xdebug-3.2.1.tgz && cd xdebug-3.2.1 && phpize && ./configure && make && make install
RUN echo "zend_extension=xdebug" > /usr/local/etc/php/conf.d/xdebug.ini

# Composer
RUN cd /tmp; curl -O https://getcomposer.org/installer && php ./installer --install-dir=/usr/local/bin --filename=composer

RUN useradd -c '' -m -d /home/u -G sudo -s /bin/bash u
RUN echo '%sudo ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers

USER u
WORKDIR /home/u

EXPOSE 4846
CMD ["/bin/bash"]
