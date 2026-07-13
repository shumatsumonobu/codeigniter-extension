FROM amazonlinux:2

RUN amazon-linux-extras install -y php7.3 \
    && yum install -y \
        php-fpm php-gd php-mbstring php-xml php-mysqli php-pdo php-zip php-json \
        ImageMagick ImageMagick-devel php-pear php-devel gcc make \
        unzip curl tar xz \
    && pecl install imagick \
    && echo "extension=imagick.so" > /etc/php.d/30-imagick.ini \
    && yum clean all && rm -rf /var/cache/yum

RUN curl -fsSL https://nodejs.org/dist/v16.20.2/node-v16.20.2-linux-x64.tar.xz \
    | tar -xJ -C /usr/local --strip-components=1

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN sed -i \
    -e 's|^listen = .*|listen = 9000|' \
    -e 's|^listen.allowed_clients|;listen.allowed_clients|' \
    -e 's|^user = .*|user = root|' \
    -e 's|^group = .*|group = root|' \
    -e 's|;*clear_env = .*|clear_env = no|' \
    /etc/php-fpm.d/www.conf

WORKDIR /var/www/html

CMD ["php-fpm", "-F"]
