Amora: simple web framework to develop web applications built with modern PHP
=============================================================================

ToDo

Amora is the translation for blackberry in [Galician](https://en.wikipedia.org/wiki/Galician_language) ([Amora in Galician Wikipedia](https://gl.wikipedia.org/wiki/Amora)).

## Why use Amora?

ToDo

## Steps to get a development machine up and running (only for Mac)

1. Install PHP: `brew install php`. Make sure you have at least PHP 8.0. `php -v` to check that PHP is running and the version.
2. Install MySql: `brew install mysql`.
3. Install Composer (a dependency manager for PHP, similar to `npm` or `yarn`). Follow the instructions here: [getcomposer.org](https://getcomposer.org/)
4. Clone this repository.
5. Run composer: go to the root folder and run `composer install`. It will create a new `vendor` folder and install all required dependencies.
6. Create a databases: run this in a terminal: `mysql -u root -p`, it will ask for the password for `root`. Then run this to create the databases: `CREATE database amora_core;CREATE database amora_mailer;CREATE database amora_action;`
7. Go to `amora/config/default.php` and update the value for `mediaBaseDir` with the right path to your code folder. Do the same for the DB parameters (`db` field).
8. Run the database migrations: `/path-to-your-code-folder/amora/Core/Bin/core_migrate_db.php migrate`
9. Sync lookup tables: `/path-to-your-code-folder/amora/Core/Bin/core_sync_lookup_tables.php`
10. Create a new user: `/path-to-your-code-folder/amora/Core/Bin/core_create_admin_user.php --email=you@domain.com --user=username --password=password`
11. Initiate a PHP server for development: `php -S localhost:8888 -t /path-to-your-code-folder/amora/public`
12. You're ready. Go to [http://localhost:8888](http://localhost:8888) (homepage) or [http://localhost:8888/admin](http://localhost:8888/admin) (Admin page). You should be able to login using the user/password created in step 10.
