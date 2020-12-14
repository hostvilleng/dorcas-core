FROM php:7.2-fpm
RUN apt-get update -y && apt-get install -y openssl zip unzip git nano
#RUN apt-get update -y && apt-get install -y openssl zip unzip git libxml2-dev curl nano

WORKDIR /var/www/dorcas-business-core

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-install pdo pdo_mysql
#RUN docker-php-ext-install pdo pdo_mysql mbstring bcmath xml ctype fileinfo json tokenizer curl


RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && \
    sed -i -e "s/^ memory_limit./memory_limit = 4G/g" -e "s/^ max_execution_time./max_execution_time = 0/g" /usr/local/etc/php/php.ini


# Install dependencies
COPY composer.json /var/www/dorcas-business-core/composer.json
RUN composer install --prefer-dist --no-scripts --no-dev --no-autoloader && rm -rf /root/.composer

# Copy codebase
COPY . /var/www/dorcas-business-core

# Finish composer
RUN composer dump-autoload --no-scripts --no-dev --optimize


RUN chown -R www-data:www-data /var/www/dorcas-business-core/storage

RUN chmod -R u=rwx,g=rwx,o=rwx /var/www/dorcas-business-core/storage
RUN chmod -R u=rwx,g=rwx,o=rw /var/www/dorcas-business-core/storage/logs
RUN touch /var/www/dorcas-business-core/storage/logs/lumen.log
RUN chown www-data:www-data /var/www/dorcas-business-core/storage/logs/lumen.log
RUN chmod u=rwx,g=rw,o=rw /var/www/dorcas-business-core/storage/logs/lumen.log
RUN chmod u=rwx,g=rx,o=x /var/www/dorcas-business-core/artisan

# RUN php artisan passport:install


RUN composer dump-autoload
#CMD php artisan serve --host=0.0.0.0 --port=18111
#EXPOSE 18001

EXPOSE 9000
CMD ["php-fpm"]