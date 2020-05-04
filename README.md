OdooApiBundle
=============

[![Build Status](https://travis-ci.org/Ang3/OdooApiBundle.svg?branch=master)](https://travis-ci.org/Ang3/OdooApiBundle) [![Latest Stable Version](https://poser.pugx.org/ang3/odoo-api-bundle/v/stable)](https://packagist.org/packages/ang3/odoo-api-bundle) [![Latest Unstable Version](https://poser.pugx.org/ang3/odoo-api-bundle/v/unstable)](https://packagist.org/packages/ang3/odoo-api-bundle) [![Total Downloads](https://poser.pugx.org/ang3/odoo-api-bundle/downloads)](https://packagist.org/packages/ang3/odoo-api-bundle)

Symfony integration of Odoo external API client v12.0. Please see [API client documentation](https://github.com/Ang3/php-odoo-api-client) for more information.

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require ang3/odoo-api-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

**This step is done automatically on symfony ```>=4.0```**

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
  public function registerBundles()
  {
    $bundles = array(
      // ...
      new Ang3\Bundle\OdooApiBundle\Ang3OdooApiBundle(),
    );

    // ...
  }

  // ...
}
```

Step 3: Configure your app
--------------------------

Depends on your symfony version, enable the configuration of the bundle:

```yaml
# app/config/config.yml or config/packages/ang3_odoo_api.yaml
ang3_odoo_api:
  default_connection: default
  connections:
    default:
      url: <database_url>
      database: <database_name>
      user: <username>
      password: <password>
```

Get started
===========

Usage
-----

First, configure your connections in the package configuration file. 
That should be done in step 3 of the installation section.

### Registry

```php
use Ang3\Bundle\OdooApiBundle\ClientRegistry;

class MyService
{
    private $clientRegistry;

    public function __construct(ClientRegistry $clientRegistry)
    {
        $this->clientRegistry = $clientRegistry;
    }
}
```

The registry contains all created clients from your configuration. It contains three useful methods:
- ```public function set(string $connectionName, Client $client): self``` Set a client by connection name.
- ```public function get(string $connectionName): Client``` Get the client of a connection. A ```\LogicException``` is thrown if the connection was not found.
- ```public function has(string $connectionName): bool``` Check if a connection exists by name.

If you don't use autowiring, you must pass the service as argument of your service:
```yaml
# app/config/services.yml or config/services.yaml
# ...
MyClass:
    arguments:
        $clientRegistry: '@ang3_odoo_api.registry'
```

### Clients

The bundle defines one client by configured connection and a public alias following this naming convention: 
```ang3_odoo_api.client.<connection_name>```.

The ```default_connection``` parameter is used to define the default client alias ```ang3_odoo_api.client``` (public).

You can get a client by dependency injection with argument autowiring. 
Run the command ```php bin/console debug:autowiring ApiClient``` to get the list of autowired clients.