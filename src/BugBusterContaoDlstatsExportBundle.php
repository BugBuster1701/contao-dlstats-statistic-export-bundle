<?php

declare(strict_types=1);

/*
 * This file is part of a BugBuster Contao Bundle
 * @copyright  Glen Langer 2008..2022 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @author     Alexander Kehr (Kehr-Solutions) <https://www.kehr-solutions.de>
 * @license    LGPL-3.0-or-later
 * @see        https://github.com/BugBuster1701/contao-dlstats-statistic-export-bundle
 */

namespace BugBuster\DlstatsExportBundle;

use BugBuster\DlstatsExportBundle\DependencyInjection\BugBusterContaoDlstatsExportExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class BugBusterContaoDlstatsExportBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new BugBusterContaoDlstatsExportExtension();
    }
}
