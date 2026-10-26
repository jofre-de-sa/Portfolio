# Imagem usada pelo Render (ou qualquer serviço com Docker) para correr o site.
FROM php:8.3-apache

RUN a2enmod rewrite headers

# O Render indica a porta na variável PORT; o Apache lê ${PORT} ao arrancar.
ENV PORT=10000
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
 && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/' /etc/apache2/sites-available/000-default.conf \
 && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

COPY . /var/www/html/
RUN rm -f /var/www/html/.env && chown -R www-data:www-data /var/www/html

EXPOSE 10000
