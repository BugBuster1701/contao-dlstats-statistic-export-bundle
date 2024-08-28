<?php

declare(strict_types=1);

/*
 * This file is part of a BugBuster Contao Bundle
 * 
 * @copyright  Glen Langer 2024 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @author     Alexander Kehr (Kehr-Solutions)
 * @package    Contao Download Statistics Bundle (Dlstats) Add-on: Statistic Export
 * @link       https://github.com/BugBuster1701/contao-dlstats-statistic-export-bundle
 */

namespace BugBuster\DlstatsExportBundle;

use BugBuster\DlstatsExportBundle\DependencyInjection\BugBusterContaoDlstatsExportExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class BugBusterContaoDlstatsExportBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface|null
    {
        return new BugBusterContaoDlstatsExportExtension();
    }
}
