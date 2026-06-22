FROM php:8.2-cli

# Installer les dépendances pour les extensions PHP et les outils système
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql

# Copier tout le projet dans le conteneur
COPY . /app

# Définir le dossier de travail
WORKDIR /app

# Exposer le port par défaut que Railway fournit (généralement 80 ou l'environnement PORT)
EXPOSE 80

# Démarrer le serveur interne ultra-léger de PHP
CMD ["php", "-S", "0.0.0.0:80", "-t", "."]