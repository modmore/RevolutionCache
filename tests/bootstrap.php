<?php

require_once dirname(dirname(__FILE__)) . '/config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('mgr');
$modx->getService('error','error.modError', '', '');

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';
