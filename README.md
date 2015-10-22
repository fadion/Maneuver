<<<<<<< HEAD
> Maneuver was recently updated to use SFTP, in addition to FTP. This change doesn't break any API, except a few simple configuration changes. You'll need to add a `scheme` option to your servers with either `ftp` or `ssh` as a value. In addition, the `username` field will have to be renamed to `user` and `password` to `pass`.

> This release is tagged with a version. Please update your composer as instructed in the [Installation](#installation) section.

> To use it in Laravel 5, please see the `l5` branch.

# Maneuver

A Laravel package that makes deployment as easy as it has never been. It uses Git to read file changes and deploys to your server(s) via FTP or SFTP. **Why Git?** Because anyone should already version their files and if they do, it's almost certain they're using Git. **Why FTP?** Because it is the easiest transport protocol to implement and use.

It is dead-simple to use! Add your servers in the config and just run one command. That's it! Anything else will be handled automatically. Oh, and it even supports Git SubModules and Sub-SubModules. Isn't that neat?

Maneuver is very tightly coupled to [PHPloy](https://Github.com/banago/PHPloy), a CLI tool written in PHP that can deploy any project, not just Laravel apps.

![maneuver](https://f.cloud.github.com/assets/374519/2333156/e0198082-a465-11e3-8fe6-f9f306597f8a.gif)

## Why?

There are plenty of fantastic tools for deployment: Capistrano, Rocketeer or Envoy (Laravel's own ssh task runner), to name a few. They get the job done, probably in a more elegant way. So why use this approach?

While any sane developer uses Git for version control, not anyone of them knows or bothers to understand how you can exploit Git to deploy. The point is that Git's domain isn't deployment, so setting it up to push to a remote repo and trigger hooks to transfer the work tree can be a tedious task. Worth mentioning is that there are still projects hosted on shared hosts, where setting up a remote Git repo may not be possible.

Maneuver solves these problems with a very simple approach. It takes the best of version control and combines it with file transfer, without needing anything special on the server. Developers have used FTP for decades to selectively upload files, in a very time consuming and error-prone process. Now they can use an automatic tool that needs only a few minutes to get started.
=======
Maneuver for Laravel 5.
>>>>>>> 524e00ff545e6efacb53cd5f88546b9485a0a567

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
