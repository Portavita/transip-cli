# transip-cli

TransIP command-line utility, extending the PHP API library.

Configuration
=====
* Go to TransIP Control Panel
* On My Account » API, be sure to exist one KeyPair.
* On My Account » API, be sure to have enabled/On the API (Status)
* Add your portal username, and the generated private key above in the file transip.credentials.php (refer to transip.credentials.php.sample);
* Be sure to have PHP installed (see tested versions below), and the following PHP modules: php-soap

Where to download the PHP Library
=================================
On 20171016, it was on version 5.5, downloaded from here: https://www.transip.nl/transip/api/

Tested with
===========
* PHP 5.4.16, on 20171016
* PHP 5.6.15, on 20171016
