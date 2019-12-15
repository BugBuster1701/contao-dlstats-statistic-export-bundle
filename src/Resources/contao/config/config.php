<?php

// Hooks
$GLOBALS['TL_DLSTATS_HOOKS']['addStatisticPanelLine'][] = ['bugbuster.dlstatsexport.listener.panel', 'onGetPanelLine'];