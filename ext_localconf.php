<?php

defined('TYPO3_MODE') or die();

$dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
    \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
);

$dispatcher->connect(
    'Extcode\Cart\Utility\OrderUtility',
    'changeOrderItemBeforeSaving',
    'Extcode\CartClubdata\Utility\OrderUtility',
    'changeOrderItemBeforeSaving'
);

$dispatcher->connect(
    'Extcode\Cart\Utility\OrderUtility',
    'checkStock',
    'Extcode\CartClubdata\Utility\OrderUtility',
    'checkStock'
);

$dispatcher->connect(
    'Extcode\Cart\Utility\ProductUtility',
    'loadCartProductFromForeignDataStorage',
    'Extcode\CartClubdata\Utility\ProductUtility',
    'loadCartProductFromForeignDataStorage'
);

if (TYPO3_MODE === 'FE') {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cart']['showCartActionAfterCartWasLoaded'][1519326733] =
        'Extcode\CartClubdata\Hooks\FrontendUserHook->showCartActionAfterCartWasLoaded';
}


