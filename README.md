# PHP Uploads Service

### Technologies
- Slim Framework [Slim framework](https://www.slimframework.com/).
- Predis [Predis](https://github.com/predis/predis/wiki).

### Requirements
- Composer ^2.7 [Composer](https://getcomposer.org/).
- Database Mysql | Postgresql | MariaDB | MSSQL Server | etc.
- PHP ^8.1 [PHP](https://www.php.net/).
- vlucas/phpdotenv [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv).
- slim/slim [slim/slim](https://github.com/slimphp/Slim).
- predis/predis [predis/predis-optional](https://github.com/predis/predis).

### How to run?
```
composer install

cp .env.example .env

php -S localhost:8000 -t public/
```

### Compatible with.
- Nginx-FPM
- Nginx-Fastcgi
- Frankenphp
- Swoole
- Workerman

### Soon.
- Benchmarks.