<?php

declare(strict_types=1);

/*
 * This file is part of a BugBuster Contao Bundle
 * @copyright  Glen Langer 2008..2019 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @author     Alexander Kehr (Kehr-Solutions) <https://www.kehr-solutions.de>
 * @license    LGPL-3.0-or-later
 * @see        https://github.com/BugBuster1701/contao-dlstats-statistic-export-bundle
 */

namespace BugBuster\DlstatsExportBundle\DependencyInjection;

use Jean85\PrettyVersions;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class BugBusterContaoDlstatsExportExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(\dirname(__DIR__).'/Resources/config')
        );

        // Common config, services and listeners
        $version = PrettyVersions::getVersion('contao/core-bundle');
        if (\Composer\Semver\Semver::satisfies($version->getShortVersion(), '>=4.7')) {
            $loader->load('services47.yml');
        } else {
            $loader->load('services.yml');
        }

        $loader->load('listener.yml');
    }
}
