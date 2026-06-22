FROM php:8.2-apache

# Installer l'extension PDO MySQL pour la base de données
RUN docker-php-ext-install pdo pdo_mysql

# Activer le module de réécriture d'Apache (souvent utile)
RUN a2enmod rewrite

# Copier tout le contenu du projet dans le dossier du serveur Apache
COPY . /var/www/html/

# Donner les bons droits d'accès aux fichiers
RUN chown -R www-data:www-data /var/www/html

# Exposer le port 80 pour que Railway puisse y accéder
EXPOSE 80