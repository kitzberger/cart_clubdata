<?php

defined('TYPO3_MODE') or die();

$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY);

$tcaPath = $extPath . 'Configuration/TCA/';
$iconPath = 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/';

$_LLL = 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_db.xlf';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    $_EXTKEY,
    'Configuration/TypoScript',
    'Cart Clubdata Connect'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_cartclubdata_domain_model_pause', 'EXT:cart_clubdata/Resources/Private/Language/locallang_csh_tx_cartclubdata_domain_model_pause.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_cartclubdata_domain_model_pause');



/**
 * Register Frontend Plugins
 */


foreach ($pluginNames as $pluginName) {
    $pluginSignature = strtolower(str_replace('_', '', $_EXTKEY)) . '_' . strtolower($pluginName);
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'Extcode.' . $_EXTKEY,
        $pluginName,
        $_LLL . ':tx_cartclubdata.plugin.' . strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($pluginName)))
    );
    $TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'select_key';

    $flexFormPath = 'EXT:' . $_EXTKEY . '/Configuration/FlexForms/' . $pluginName . 'Plugin.xml';
    if (file_exists(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($flexFormPath))) {
        $TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
            $pluginSignature,
            'FILE:' . $flexFormPath
        );
    }
}



    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'Extcode.' . $_EXTKEY,
    'Cart',
    'Backend',
    '',
    [
        'Backend' => 'interface, ticketExport, ticketCheck, ticketCheckDetail, ticketCheckWrite, refundCheckOrders, refundOrders, refundOrder',
    ],
    [
        'access' => 'user, group',
        'icon' => $iconPath . 'module_cartclubdata.svg',
        'labels' => $_LLL . ':tx_cartclubdata.module.clubdata',
        'navigationComponentId' => 'typo3-pagetree',
    ]
);

