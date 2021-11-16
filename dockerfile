FROM php:7.4-fpm
#RUN apt-get update -y && apt-get install -y openssl zip unzip git nano libonig-dev
RUN apt-get update -y && apt-get install -y openssl zip unzip git libxml2-dev curl nano libonig-dev build-essential libcurl4-openssl-dev

WORKDIR /var/www/dorcas-core

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ctype fileinfo mysqli json tokenizer
#RUN docker-php-ext-install pdo pdo_mysql mbstring bcmath xml curl && docker-php-ext-enable pdo_mysql

# Automated Recommeded Method to install extensionn
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions gd pdo pdo_mysql mbstring bcmath xml curl ctype fileinfo mysqli json tokenizer


RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && \
    sed -i -e "s/^ memory_limit./memory_limit = 4G/g" -e "s/^ max_execution_time./max_execution_time = 0/g" /usr/local/etc/php/php.ini


# Install dependencies
COPY composer.json /var/www/dorcas-core/composer.json
RUN composer install --prefer-dist --no-scripts --no-dev --no-autoloader && rm -rf /root/.composer

# Copy Appropriate ENV
#COPY .env.docker /var/www/dorcas-core/.env

# Copy codebase
COPY . /var/www/dorcas-core

# Finish composer
RUN composer dump-autoload --no-scripts --no-dev --optimize


RUN chown -R www-data:www-data /var/www/dorcas-core/storage

RUN chmod -R u=rwx,g=rwx,o=rwx /var/www/dorcas-core/storage
RUN chmod -R u=rwx,g=rwx,o=rw /var/www/dorcas-core/storage/logs
RUN touch /var/www/dorcas-core/storage/logs/lumen.log && > /var/www/dorcas-core/storage/logs/lumen.log
RUN chown www-data:www-data /var/www/dorcas-core/storage/logs/lumen.log
RUN chmod u=rwx,g=rw,o=rw /var/www/dorcas-core/storage/logs/lumen.log
RUN chmod u=rwx,g=rx,o=x /var/www/dorcas-core/artisan

RUN chmod 660 /var/www/dorcas-core/storage/oauth-public.key

# RUN php artisan passport:install

RUN mkdir -p /var/log/php/ && touch /var/log/php/dorcas.log

RUN composer dump-autoload
#CMD php artisan serve --host=0.0.0.0 --port=18111
#EXPOSE 18001

EXPOSE 9000
CMD ["php-fpm"]