# Installation von Contao 5 dlstats Bundle Add-on: Statistik Export

Es gibt zwei Arten der Installation.

* mit dem Contao-Manager, für die Contao Managed-Editon
* über die Kommandozeile, für die Contao Managed-Editon


## Installation über Contao-Manager

* Suche das Paket: `bugbuster/contao-dlstats-statistic-export-bundle`
* Installation der Erweiterung
* Datenbank Update durchführen

Wenn Du "... symfony/form x.x.x conflicts with ..." dabei siehst, mußt Du ein komplettes Paket-Update durchführen. Markiere dazu alle Pakete zum aktualisieren.


## Installation über die Kommandozeile

Installation in einer Composer-basierenden Contao 5.2+ Managed-Edition:

* `composer require "bugbuster/contao-dlstats-statistic-export-bundle"`
* `php bin/console contao:migrate`
