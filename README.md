# dns-updater
Tool to update dns records through Direct Admin API.

This tool is build to be run on a homeserver with an internet connection that has a dynamic ip address.

It will check the external ip address and if the address has changed from the last run, it will update the given dns 
records (subdomain A-records) with this new ip address.

You should create a crontab entry for this and run it a few times a day.

## Installation

1. Clone this repo
2. composer install
3. copy config.yml.dist to config.yml
4. edit settings in config.yml
5. run app.php updater