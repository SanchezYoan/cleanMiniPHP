# cleanMiniPHP

Backoffice MVC léger en PHP pur, sans framework. Conçu pour être simple, sécurisé et facilement extensible.

## Stack


Backend: PHP 8.3, MVC custom, PDO (MySQL/MariaDB) |
Frontend: Tabler UI (Bootstrap), ApexCharts, Dropzone |
Auth: Sessions, 2FA (Google Authenticator), CSRF |
Outils: PHPMailer, Guzzle, Whoops (dev), PHPUnit |

## Fonctionnalités

- Authentification avec double facteur (2FA)
- Gestion des utilisateurs (CRUD, reset password, verrouillage)
- Rôles : `USER`, `ADMIN`, `ADMINSU`
- Logger multi-niveaux (fichier, base de données, email)
- API mobile (validation device, headers sécurisés)
- Tâches planifiées (crons)
- Dashboard avec graphiques

## Installation

**Prérequis :** PHP 8.3+, Composer, Node.js, MySQL/MariaDB

```bash
git clone <repo>
cd cleanMiniPHP/www
composer install
cd public/assets && npm install
```

Créer la base de données et configurer dans `app/config/config.php` :

```php
DB_HOST, DB_NAME, DB_USER, DB_PASS
```

Lancer le serveur :

```bash
php -S localhost:8080 -t www/public/
```


