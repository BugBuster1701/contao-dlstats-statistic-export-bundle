<?php

declare(strict_types=1);

/*
 * This file is part of a BugBuster Contao Bundle.
 *
 * @copyright  Glen Langer 2024 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @author     Alexander Kehr (Kehr-Solutions)
 * @package    Contao Download Statistics Bundle (Dlstats) Add-on: Statistic Export
 * @link       https://github.com/BugBuster1701/contao-dlstats-statistic-export-bundle
 *
 * @license    LGPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace BugBuster\DlstatsExportBundle\ContaoManager;

use BugBuster\DlstatsBundle\BugBusterDlstatsBundle;
use BugBuster\DlstatsExportBundle\BugBusterContaoDlstatsExportBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\Config\ExtensionPluginInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class Plugin implements BundlePluginInterface, ExtensionPluginInterface, RoutingPluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(BugBusterContaoDlstatsExportBundle::class)
                ->setLoadAfter(
                    [
                        ContaoCoreBundle::class,
                        BugBusterDlstatsBundle::class,
                    ],
                ),
        ];
    }

    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container)
    {
        if ('framework' === $extensionName) {
            $extensionConfigs[] = [
                'form' => true,
            ];
        }

        return $extensionConfigs;
    }

    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
    {
        return $resolver
            ->resolve(\dirname(__DIR__).'/Resources/config/routing.yml')
            ->load(\dirname(__DIR__).'/Resources/config/routing.yml')
        ;
    }
}
