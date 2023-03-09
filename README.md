Framework to Develop Web Applications
=============================================================================

Amora is a framework written in PHP 8.2 to develop web applications with a focus on simplicity and performance. It is highly customizable, written in a modular way, so that it can be easily extended to support new features. It is highly influenced by functional programming principles.

It comes with a photo-blog CMS out of the box.

Amora is the translation for blackberry in [Galician](https://en.wikipedia.org/wiki/Galician_language).

## How it works?

There are four main folders: [Core](/Core), where the core files and functionality lives; [App](/App), the place to write your application code and extend the functionality without affecting the core; [public](/public), where the static files (CSS, images and javascript) and `index.php` live and [view](/view), where the templates live.

It is a work in progress. If you have any questions, please do not hesitate to contact me.

## Steps to get a development machine up and running

1. Install the latest version of PHP. Linux: `sudo apt-get install php8.2`, Mac: `brew install php`. Make sure you have at least PHP 8.2. Once the installation has finished run `php -v` to check that PHP is running and the version.
2. Install the latest version of MariaDB/MySql. Linux: `sudo apt-get install mysql-server`, Mac: `brew install mysql`. Make sure you have at least MariaDB 10 or MySQL 8.0. Once the installation has finished run `mysql -V` (or `/usr/local/mysql/bin/mysql -V` in some cases) to check that MariaDB/MySQL is running and the version.
3. Install [Composer](https://getcomposer.org/) (a dependency manager for PHP, similar to `npm`, `yarn`, `cargo`, ...). Follow the instructions here: [getcomposer.org](https://getcomposer.org/)
4. Clone this repository: `git clone git@github.com:uveic/amora.git`
5. Run composer: go to the root folder and run `composer install`. It will create a new `vendor` folder and install all required dependencies.
6. Create databases: `mysql -u root -p`, it will ask for the password for `root`. Then run this command to create the databases: `CREATE database amora_core;CREATE database amora_mailer;CREATE database amora_action;`
7. Go to `Amora/App/Config/AppConfig.php` and update the value for `mediaBaseDir` with the right path to your code folder. Do the same for the DB parameters (`db` field).
8. Run the database migrations: `/path-to-your-code-folder/amora/Core/Bin/devops/migrate.sh`
9. Create a new user: `/path-to-your-code-folder/amora/Core/Bin/core_create_admin_user.php --email=you@domain.com --user=username --password=password`
10. Initiate a PHP server for development: `php -S localhost:8888 -t /path-to-your-code-folder/amora/public`
11. You're ready. Go to the homepage ([http://localhost:8888](http://localhost:8888)) or the login page ([http://localhost:8888/login](http://localhost:8888/login)). You should be able to log in using the user/password created in step 9.

## Why this project?

It started as a personal project mainly because I wanted to build something that I could use as the base of some of my projects while I learned the intricates of frameworks and web development.

## Known issues

This software is largely untested, undocumented, and unoptimized.

## License

Licensed under the MIT License. See [LICENSE](/LICENSE) for more information.

## Thanks

If you find it useful, [say thanks buying me a beer 🍺](https://www.paypal.com/paypalme/uveic).
