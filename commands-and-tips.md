# Server Configuration & Other Useful Commands

## PHP

Display path to php.ini: `php --ini`

List all installed modules: `php -m`

Path to `php.ini`: `/etc/php/8.2/fpm/php.ini`

Important settings in `php.ini`: `display_errors = off`, `error_reporting = E_ALL`, `upload_max_filesize=32M`, `post_max_size=32M`, `max_execution_time = 300`, `zend.multibyte = On`

Error/access logs (setup in nginx config file in `/etc/nginx/sites-available/site-name.conf`):

Example: `access_log /var/log/nginx/project-name-access.log;`, `error_log /var/log/nginx/project-name-error.log warn;`

### Package dependency:

`sudo apt-get install php8.2-gd php8.2-mysql php8.2-fpm php8.2-curl php8.2-xml php8.2-cli php8.2-common php8.2-opcache`

`php8.2-fpm`\
`php8.2-mysql`\
`php8.2-curl`\
`php8.2-gd`\
`php8.2-cli`\
`php8.2-common`\
`php8.2-opcache`\
`php8.2-mbstring`

Restart PHP-FPM:\
`sudo service php8.2-fpm restart`\
`sudo /etc/init.d/php8.2-fpm restart`

PHP-FPM config: `/etc/php/8.2/fpm/php-fpm.conf`

Pool config: `/etc/php/8.2/fpm/pool.d/www.conf`

Optimizing PHP-FPM for High Performance: https://geekflare.com/php-fpm-optimization/

Relevant settings: `catch_workers_output = yes`

## nginx

Path to config file: `/etc/nginx/nginx.conf`

Relevant settings in `nginx.conf`: `client_max_body_size 32M;`, `client_body_buffer_size 32M;`

How to enable a site that is configured in "sites-available": `ln -s /etc/nginx/sites-available/www.example.org.conf /etc/nginx/sites-enabled/`

Test nginx configuration file(s): `sudo nginx -t`

Restart nginx: `sudo /etc/init.d/nginx restart`

Configuring the Nginx Error and Access Logs: https://www.journaldev.com/26756/nginx-access-logs-error-logs

## vi
Search forward: press `/`, type the search pattern and press `Enter`

Find next: press `n` (after the first `Enter`)

Search backwards: press `?`, type the search pattern and press `Enter`

`dd` => Delete line.

## Ubuntu / Linux
Reboot: `sudo shutdown -r`

Check Ubuntu version: `cat /etc/os-release`

## Let's Encrypt

`sudo certbot`

Test automatic renewal: `sudo certbot renew --dry-run`

## Other Useful Commands
Sync files from my local computer to a server through SSH: `rsync -a -e "ssh" --exclude 'path/to/file.txt' --exclude 'path-to-folder/*' --exclude 'README.md' ~/path/to/local/folder/* user@server-ip-address:/path/to/remote/folder/`

Figure out disk space: `du -hx --max-depth=1 /var/www`

How to copy a file(s) from a remote host through ssh: 
`scp user@server-ip-address:/path/to/remote/file.ext /path/to/local/folder`