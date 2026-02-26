## Lien vers la PROD

https://r301.kilya.coop/

Identifiants:
admin admin

## Configuration Apache

### MODs à installer

php
php-mysql
rewrite

### Configuration du virtual host

```
Listen 8081

<VirtualHost *:8081>
    ServerName localhost
    DocumentRoot C:/xampp/htdocs/R4.01-ProjetAPI-Server

    <Directory "C:/xampp/htdocs/R4.01-ProjetAPI-Server">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

</VirtualHost>
```

## Technologies utilisées

- HTML
- CSS
- PHP
- PDO (pour la gestion de la base de données)
- MySQL
