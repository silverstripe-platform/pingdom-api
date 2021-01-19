Pingdom API
===========

This package is a PHP interface to the Pingdom REST API.

Installation
------------

Run the following commands to install the required dependencies:

```bash
cd <project root>
composer install --no-dev
```

Usage
-----

```php
require 'vendor/autoload.php';
use Silverstripe\Pingdom\Api;

$pingdom = new Api('api_key');
print_r($pingdom->getChecks());
```

Cli
-----

For functional testing you can use the `get-checks` command provided as a symfony console command

usage:

```bash
export PINGDOM_API_TOKEN="somesecretgoeshere"
./bin/pingdom-cli get-checks
```

Running the tests
-----------------

The following commands can be used to run the test suite locally:

```bash
cd <project root>
composer update
./vendor/bin/phpunit
```

Using `composer update` with the `--dev` flag will download the phpunit dependency.

This is a continuation / fork of [https://github.com/stojg/pingdom-api] and the discontinued Acquia library found at [https://github.com/acquia/pingdom-api]
