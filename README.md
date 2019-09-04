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

```yaml
# app/config/config.yml
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

## Usage

The bundle create one client by configured connection and a public alias following this naming convention: ```ang3_odoo_api.client.<connection_name>```.

The ```default_connection``` parameter is used to define the default client alias ```ang3_odoo_api.client``` (public).