# Maneuver

A Laravel package that makes deployment as easy as it has never been. It uses Git to read file changes and deploys to your server(s) via FTP. **Why Git?** Because anyone should already version their files and if they do, it's almost certain they're using Git. **Why FTP?** Because it is the easiest transport protocol to implement, but others (SFTP, ssh) will be rolling soon.

It is dead-simple to use! Add your servers in the config and just run one command. That's it! Anything else will be handled automatically. Oh, and it even supports Git SubModules and Sub-SubModules. Isn't that neat?

Maneuver is very tightly coupled to [PHPloy](https://Github.com/banago/PHPloy), a CLI tool written in PHP that can deploy any project, not just Laravel apps.

![maneuver](https://f.cloud.github.com/assets/374519/2333156/e0198082-a465-11e3-8fe6-f9f306597f8a.gif)

## Why?

There are plenty of fantastic tools for deployment: Capistrano, Rocketeer or Envoy (Laravel's own ssh task runner), to name a few. They get the job done, probably in a more elegant way. So why use this approach?

While any sane developer uses Git for version control, not anyone of them knows or bothers to understand how you can exploit Git to deploy. The point is that Git's domain isn't deployment, so setting it up to push to a remote repo and trigger hooks to transfer the work tree can be a tedious task. Worth mentioning is that there are projects hosted on shared hosts, where setting up a remote Git repo may not be possible.

Maneuver solves these problems with a very simple approach. It takes the best of version control and combines it with file transfer, without needing anything special on the server. Developers have used FTP for decades to selectively upload files, in a very time consuming and error-prone process. Now they can use an automatic tool that needs only a few minutes to get started.

## Installation

1. Add the package to your composer.json file and run `composer update`:

```json
{
    "require": {
        "fadion/maneuver": "dev-master"
    }
}
```

2. Add `Fadion\Maneuver\ManeuverServiceProvider` to your `app/config/app.php` file, inside the `providers` array.

3. Publish the package's config with `php artisan config:publish fadion/maneuver`, so you can easily modify it in: `app/config/packages/fadion/maneuver/config.php`

## Configuration

The first step is to add servers in the configuration file. If you followed step 3 above, you'll find it in `app/config/packages/fadion/maneuver/config.php`.

Add one or more servers in the `connections` array, providing a unique, recognizable name for each. Credentials should obviously be entered too. Optionally, specify a default server for deployment, by entering the server's name in the `default` option. Changes will be deployed to that server if not overriden. In case you leave the `default` option empty, deployment will be run to all the servers.

## Usage

You'll use Maneuver from the command line, in the same way you run other `artisan` commands. Even if the terminal feels like an obscure environment that you can't wrap your head around, I assure it'll be a piece of cake.

From now on I'll assume that you already have a local Git repository and that you've commited some changes. Maneuver uses information from Git to get file changes and it won't work if there's no Git repository. Also, I'll assume you've opened a terminal (command prompt or whatever your OS calls it) and are located in the root directory of your Laravel app.

### Deployment

The command you'll be using all the time, mostly without any arguments, is:

    php artisan deploy

That command will read the Git repo, build a list of files to upload and/or delete and start transferring them via FTP. If you've specified a `default` server in the config file, it will push to it, otherwise it will push to all the servers you've defined. It's also quite verbose, printing useful information on each step of the process.

### Servers

Instead of pushing to the default server, you can deploy to one or more servers of your choice, by passing arguments to the `deploy` command. Supposing we have defined a development, staging and production server, with 'development' being the default, we can deploy only to 'staging' with:

    php artisan deploy --server=staging

By passing multiple options, we can tell it to deploy to both 'staging' and 'production' server:

    php artisan deploy --server=staging --server=production

There's even a shortcut option:

    php artisan deploy -s staging -s production

### List Changed Files

For convenience, you can view a list of changed files since your last deployment. Just type:

    php artisan deploy:list

As in the `deploy` command, it will list the changed files of your default server. You can pass the server options here to:

    php artisan deploy:list --server=staging

As you've guessed, running this command will still connect to your server(s), but no uploads will be done. It just compares revisions and lists those changed files.

### Rollback

Rolling back is a facility, which does the heavy lifting for you by moving temporarily to a previous commit and deploying those files. After the run, the Git repo will be reverted as it was before the command. Use it as a quick way to rollback your files in the server, but not as a way to modify your Git repo.

To rollback to the previous commit, just run:

    php artisan deploy:rollback

If you want to rollback to a specific commit, type:

    php artisan deploy:rollback --commit=<hash>

### Remote Revision File

To achieve synchronization, Maneuver will store a `.revision` file in your server(s), which contains the hash of the latest deployment commit. Deleting that file will trigger a fresh deployment and all your files will be transfered again. Editing it's contents should be avoided, otherwise very strange things may happen.

## Authors

**Fadion Dashi** is the author of Maneuver. He's a PHP, Laravel and Objective-C developer that lives in Tirana, Albania and runs his own company at [Streha shpk](http://www.streha.al).

**Baki Goxhaj** is the author of PHPloy, on which Maneuver is based. He's a freelance PHP, Laravel and Wordpress developer that lives in Vlora, Albania. Find him at [WPLancer](http://www.wplancer.com).
