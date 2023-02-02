<?php


defined('TYPO3_MODE') || die('Access denied.');

use Ressourcenmangel\Rsmoembed\Hooks\GetOembedDataHook;

call_user_func(
    function () {

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']
        [GetOembedDataHook::class] = GetOembedDataHook::class;

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:rsmoembed/Configuration/TsConfig/All.tsconfig">'
        );

        // Register a node in ext_localconf.php
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][16753462650] = [
            'nodeName' => 'RsmOembedData',
            'priority' => 50,
            'class' => \Ressourcenmangel\Rsmoembed\Form\Element\RsmOembedData::class,
        ];

        // autoload Embed
        $composerAutoloadFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rsmoembed')
            . 'Resources/Private/PHP/embed/vendor/autoload.php';
        require_once($composerAutoloadFile);

    }
);
