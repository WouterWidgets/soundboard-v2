Soundboard V2
=============
WORK IN PROGRESS

Requirements
------------
- PHP 7+
- NodeJS + NPM
- youtube-dl (optional for YouTube editor) `sudo apt install youtube-dl`
- ffmpeg (optional for YouTube editor) `sudo apt install ffmpeg`

Installation
------------
- Copy `config.json.dist` to `config.json` and modify it to your needs.
- Run `npm i` (this may take a while, so alternatively you may install locally and upload `node_modules` instead)
- Run `npx gulp production` (or `gulp production` if you have gulp installed globally)
- You probably want to update the `upload_max_filesize` and `post_max_size` in `php.ini` if it is still set to the default 2/8Mb.

Serve
-----
- Run `php -S 0.0.0.0:8000 -t /var/www/public_html/` or serve from apache/nginx or whatever

Raspberry Pi installation
-------------------------

### PHP
- Run `sudo apt install php php-mbstring`

### NodeJS + NPM
- Run `sudo apt install -y nodejs`
- Run `sudo apt install -y npm`
- Run `sudo npm i -g npm`

### VLC
- Run `sudo apt install -y vlc`

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

### Misc tips for running it on Raspberry Pi
- You might want to set the 3.5mm audio as the default audio device.
- Increase volume using `amixer sset PCM,0 90%`
- If you want to hide the mouse cursor, follow these steps:
    - Run `sudo apt-get install unclutter`
    - Run `nano ~/.config/lxsession/LXDE-pi/autostart` (or `sudo nano /etc/xdg/lxsession/LXDE-pi/autostart` depending on your Raspbian version)
    - Add this line: `@unclutter -idle 0` and save changes
    - Reboot your device (`sudo reboot`)
