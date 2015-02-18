Maneuver for Laravel 5.

## Installation

1. Add the package to your composer.json file and run `composer update`:

```json
{
    "require": {
        "fadion/maneuver": "dev-l5"
    }
}
```

2. Add `Fadion\Maneuver\ManeuverServiceProvider` to your `config/app.php` file, inside the `providers` array.

3. Publish the package's config with `php artisan vendor:publish`, so you can easily modify it in: `config/maneuver.php`

## Configuration

The first step is to add servers in the configuration file. If you followed step 3 above, you'll find it in `config/maneuver.php`.

Add one or more servers in the `connections` array, providing a unique, recognizable name for each. Credentials should obviously be entered too. Optionally, specify a default server for deployment, by entering the server's name in the `default` option. Changes will be deployed to that server if not overriden. In case you leave the `default` option empty, deployment will be run to all the servers.

Don't forget to set the `scheme` for your servers to either `ftp` or `ssh` for SFTP.
