<?php

/*
 * This file is part of a BugBuster Contao Bundle.
 *
 * @copyright  Glen Langer 2023 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @author     Alexander Kehr (Kehr-Solutions)
 * @package    Contao Download Statistics Bundle (Dlstats) Add-on: Statistic Export
 * @link       https://github.com/BugBuster1701/contao-dlstats-statistic-export-bundle
 *
 * @license    LGPL-3.0-or-later
 */

$GLOBALS['TL_DLSTATS_HOOKS']['addStatisticPanelLine'][] = array('bugbuster.dlstatsexport.listener.panel', 'onGetPanelLine');
