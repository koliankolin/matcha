FROM phpstorm/php-71-apache-xdebug

# Load extra Apache modules
RUN a2enmod rewrite

# Installs sendmail
RUN apt-get update && apt-get install -q -y ssmtp mailutils && rm -rf /var/lib/apt/lists/* && apt-get install -y ca-certificates

RUN apt-get update && \
    apt-get upgrade -y && \
	apt-get install -y ca-certificates openssh-server libssl-dev ufw sudo

# Installs php modules
RUN docker-php-ext-install mysqli pdo pdo_mysql

#RUN mkdir /var/www/html/src
RUN mkdir /var/www/html/data && chown -R www-data:www-data /var/www/html/data


RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/ssl-cert-snakeoil.key -out /etc/ssl/certs/ssl-cert-snakeoil.pem -subj "/C=AT/ST=Vienna/L=Vienna/O=Security/OU=Development/CN=www"

RUN a2enmod rewrite
RUN a2ensite default-ssl
RUN a2enmod ssl

#
RUN echo 'ServerName www' >> /etc/apache2/apache2.conf


#RUN a2enmod alias
#RUN service apache2 restart
#RUN echo 'Redirect / https://192.168.22.27:8001/' >> /etc/apache2/sites-enabled/000-default.conf

# set up sendmail config, see http://linux.die.net/man/5/ssmtp.conf for options
RUN echo "hostname=mail" > /etc/ssmtp/ssmtp.conf
RUN echo "root=root" >> /etc/ssmtp/ssmtp.conf
RUN echo "mailhub=mail" >> /etc/ssmtp/ssmtp.conf
# The above 'maildev' is the name you used for the link command
# in your docker-compose file or docker link command.
# Docker automatically adds that name in the hosts file
# of the container you're linking MailDev to.

# Set up php sendmail config
RUN echo "sendmail_path=sendmail -i -t" >> /usr/local/etc/php/conf.d/docker-php-sendmail.ini

RUN echo "localhost mail" >> /etc/hosts

EXPOSE 80
EXPOSE 443