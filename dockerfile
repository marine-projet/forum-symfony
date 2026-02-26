# Utilise l'image officielle de PHP
FROM php:8.2-fpm

# Mise à jour des paquets et installation des dépendances requises
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install intl pdo pdo_mysql opcache zip

# Installation de Composer (le gestionnaire de dépendances PHP)
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Configuration du dossier de travail, où le code Symfony sera présent
WORKDIR /app

# Copie tout le code source du projet Symfony dans le conteneur
COPY . .

# Installation des dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Exposition du port par défaut du serveur web
EXPOSE 9000

# Commande par défaut pour démarrer PHP-FPM
CMD ["php-fpm"]