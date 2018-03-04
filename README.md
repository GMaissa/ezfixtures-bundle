# eZ (publish) Fixtures Bundle

## About

An eZ publish bundle to manage fixtures using [kaliop/ezmigrationbundle](https://github.com/kaliop-uk/ezmigrationbundle).

## Installation

The recommended way to install this bundle is through [Composer](http://getcomposer.org/). Just run :

```bash
composer require gmaissa/ezfixturesbundle
```

Register the bundle in the kernel of your application :

```php
// ezpublish/EzPublishKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new GMaissa\EzFixturesBundle\GMEzFixturesBundle(),
    );

    return $bundles;
}
```

## License

This bundle is released under the MIT license. See the complete license in the bundle:

```bash
LICENSE
```
