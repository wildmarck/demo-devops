# On utilise une version de PHP compatible avec ton code
FROM php:8.2-apache

# Installation des extensions nécessaires pour ta base de données
# Ton code utilise PDO (vu dans config.php), on doit donc l'activer
RUN docker-php-ext-install pdo pdo_mysql

# Activation du module de réécriture d'Apache (utile pour le futur)
RUN a2enmod rewrite

# Copie de tous tes fichiers dans le serveur web du conteneur
COPY . /var/www/html/

# On donne les droits au serveur web pour lire les fichiers
RUN chown -R www-data:www-data /var/www/html

# Le port 80 est celui par défaut du web
EXPOSE 80