# KosKu

Backend API untuk aplikasi KosKu, dibangun dengan Laravel.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## Tentang Proyek

KosKu adalah aplikasi backend berbasis Laravel untuk manajemen kost/boarding house.

## Requirements

- PHP >= 8.2
- Composer
- PostgreSQL

## Instalasi

```bash
git clone https://github.com/Sype212/Backend-KosKu.git
cd Backend-KosKu

composer install

cp .env.example .env
php artisan key:generate

php artisan migrate
php artisan db:seed
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
