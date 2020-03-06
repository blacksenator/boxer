# An access base for AVM FRITZ!Box

A platform to test different ways to access, get and post data from/to the FRITZ!Box with http requests.
The base is the API from [andig](https://github.com/andig/carddav2fb/blob/master/src/FritzBox/Api.php), which has been extended by a few routines.
The example code shows how to change the kid protection filter of a designated device.

## Requirements

* PHP ≥7.0 (`apt-get install php php-curl php-mbstring php-xml`)
* Composer (follow the installation guide at <https://getcomposer.org/download/)

## Installation

Install requirements

    git clone https://github.com/blacksenator/boxer.git
    cd boxer
    composer install --no-dev

edit `config.example.php` and save as `config.php`

## Usage

    php boxer run

## License

This script is released under MIT license.

## Author

Copyright (c) 2019 Volker Püschel, Andreas Götz
