FROM php:7.2-fpm
RUN apt-get update -y && apt-get install -y openssl zip unzip git nano
#RUN apt-get update -y && apt-get install -y openssl zip unzip git libxml2-dev curl nano

WORKDIR /var/www/dorcas-business-core

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


# RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer && \
#  composer \
# global require hirak/prestissimo --no-plugins --no-scripts

#RUN phpdismod xdebug

RUN docker-php-ext-install pdo pdo_mysql
#RUN docker-php-ext-install pdo pdo_mysql mbstring bcmath xml ctype fileinfo json tokenizer curl

##https://github.com/emcniece/docker-wordpress/blob/master/Dockerfile


RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && \
sed -i -e "s/^ memory_limit./memory_limit = 4G/g" -e "s/^ max_execution_time./max_execution_time = 0/g" /usr/local/etc/php/php.ini


# Install dependencies
COPY composer.json /var/www/dorcas-business-core/composer.json
RUN composer install --prefer-dist --no-scripts --no-dev --no-autoloader && rm -rf /root/.composer

# Copy codebase
COPY . /var/www/dorcas-business-core

# Finish composer
RUN composer dump-autoload --no-scripts --no-dev --optimize




#COPY . /var/www/dorcas-business-core
#RUN chown -R admin:admin /app
#RUN chmod 755 /app

RUN chown -R www-data:www-data /var/www/dorcas-business-core/storage

RUN chmod -R u=rwx,g=rwx,o=rwx /var/www/dorcas-business-core/storage
RUN chmod -R u=rwx,g=rwx,o=rw /var/www/dorcas-business-core/storage/logs
RUN touch /var/www/dorcas-business-core/storage/logs/lumen.log
RUN chown www-data:www-data /var/www/dorcas-business-core/storage/logs/lumen.log
RUN chmod u=rwx,g=rw,o=rw /var/www/dorcas-business-core/storage/logs/lumen.log
RUN chmod u=rwx,g=rx,o=x /var/www/dorcas-business-core/artisan


#RUN composer install
#CMD php artisan serve --host=0.0.0.0 --port=18111
#EXPOSE 18001

EXPOSE 18111
CMD ["php-fpm"]