# Installation of Contao 5 dlstats Bundle Add-on: Statistic Export

There are two types of installation.

* with the Contao-Manager, for Contao Managed-Editon
* via the command line, for Contao Managed-Editon


## Installation with Contao-Manager

* search for package: `bugbuster/contao-dlstats-statistic-export-bundle`
* install the package
* update the database

If you see "... symfony/form x.x.x conflicts with ...", you need to do a complete package update. Mark all your packages to be updated.


## Installation via command line

Installation in a Composer-based Contao 5.2+ Managed-Edition:

* `composer require "bugbuster/contao-dlstats-statistic-export-bundle"`
* `php bin/console contao:migrate`
