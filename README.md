Soundboard V2
=============
WORK IN PROGRESS

Requirements
------------
- PHP 7+
- NodeJS + NPM

Installation
------------
- Run `npm i` (this may take a while, so alternatively you may install locally and upload `node_modules` instead)
- Run `npx gulp production` (or `gulp production` if you have gulp installed globally)

Serve
-----
- Run `php -S 0.0.0.0:8000 -t public_html/` or serve from apache/nginx or whatever

Raspberry Pi installation
-------------------------

### Apache
- Run `sudo apt-get update`
- Run `sudo apt install -y apache2`
- Run `sudo chown -R pi:www-data /var/www/`
- Run `sudo chmod -R 770 /var/www/`
- Run `sudo nano /etc/apache2/sites-enabled/000-default.conf`
- Find the line `/var/www/html` and update it to `/var/www/public_html`
- Run `sudo service apache2 restart`
- Run `sudo rm -f /var/www/html/index.html`

### PHP
- Run `sudo apt install php php-mbstring`

### NodeJS + NPM
- Run `sudo apt install -y nodejs`
- Run `sudo apt install -y npm`
- Run `sudo npm i -g npm`

### Install application
- Run `git clone https://github.com/WouterWidgets/soundboard-v2 /var/www`
- Follow this README.md

### FTP access to upload files (sounds)
- Run `sudo apt install -y pure-ftpd`
- Run `sudo groupadd ftpgroup`
- Run `sudo useradd ftpuser -g ftpgroup -s /sbin/nologin -d /dev/null`
- Run `sudo chown -R ftpuser:ftpgroup /var/www/public_html/files`
- Run `sudo pure-pw useradd soundboard -u ftpuser -g ftpgroup -d /var/www/public_html/files -m`
- Enter a password for the user, e.g. `soundboard`
- Run `sudo pure-pw mkdb`
- Run `sudo ln -s /etc/pure-ftpd/conf/PureDB /etc/pure-ftpd/auth/60puredb`
- Run `sudo service pure-ftpd restart`