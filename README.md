<p align="center">
    <h1 align="center">Dariobouman</h1>
</p>

## Installation

To Install & Run This Project You Have To Follow Thoose Following Steps:

```sh
git clone https://github.com/akibur-rahman-wix-buddy/zaaaan7.git
```

```sh
cd zaaaan7
```

```sh
composer install
```

Open your `.env` file and change the database name (`DB_DATABASE`) to whatever you have, username (`DB_USERNAME`) and password (`DB_PASSWORD`) field correspond to your configuration

```sh
php artisan key:generate
```

```sh
php artisan migrate
```

```sh
php artisan storage:link
```

```sh
php artisan db:seed
```

```sh
php artisan optimize
```

```sh
php artisan serve
```