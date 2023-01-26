# Netgen Layouts & Remote Media integration installation instructions

## Use Composer to install the integration

Run the following command to install Netgen Layouts & Remote Media integration:

```
composer require netgen/layouts-remote-media
```

## Activate the bundle

Activate the bundle in your kernel class. Make sure it is activated after all
other Netgen Layouts and Content Browser bundles:

```
...
...

$bundles[] = new Netgen\Bundle\LayoutsRemoteMediaBundle\LayoutsRemoteMediaBundle();

return $bundles;
```

## Configure the bundle

### Cache configuration

This bundle uses cache to store next cursor when fetching remote media, due to incompatibility between cursor-based pagination in Remote Media and limit/offset based pagination in Netgen Layouts.

You can manually configure cache pool as well as desired TTL:


```yaml
netgen_layouts_remote_media:
    cache:
        pool: cache.app
        ttl: 7200
```

Above shown are the default used parameters. For more information about creating and configuring cache pools, see https://symfony.com/doc/current/cache.html.
