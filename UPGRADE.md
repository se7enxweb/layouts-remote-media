Netgen Layouts Remote Media upgrade instructions
===============================================

Upgrade from 1.0 to 2.0
-----------------------

### Netgen Remote Media upgrade

Version 2 of this bundle uses Netgen Remote Media version 3 which differs a lot than version 2, which is being used by version 1 of this bundle so first read [Netgen Remote Media upgrade instructions](https://github.com/netgen/NetgenRemoteMediaBundle/blob/3.0/docs/UPGRADE.md#upgrade-from-20-to-30)!

### Configuration changes

#### Cache configuration

From version 2, this bundle uses cache to store next cursor when fetching remote media, due to incompatibility between cursor-based pagination in Remote Media and limit/offset based pagination in Netgen Layouts.

You have to add configuration for cache pool as well as desired TTL:

```yaml
netgen_layouts_remote_media:
    cache:
        pool: cache.app
        ttl: 7200
```

Above shown are the default used parameters. For more information about creating and configuring cache pools, see https://symfony.com/doc/current/cache.html.

### Database changes

#### Import new database tables

Version 2 stores used resources in a separate table in the database. Use the following command to add the tables to your database:

```
 mysql -u<user> -p<password> -h<host> <db_name> < vendor/netgen/layouts-remote-media/bundle/Resources/sql/mysql/schema.sql
```

#### Database values migration

The structure of the database has slightly changed and currently added items and blocks won't work with the version 2. You have to run a command which will automatically fix this for you:

```php
php bin/console netgen-layouts:remote-media:migrate-v1-to-v2
```
