<?php
defined('TYPO3') || die();

(static function() {
    // Add Icons begin

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );

    $iconKeys = [
        'rsmoembed_default',
    ];

    foreach ($iconKeys as $iconKey) {
        $iconRegistry->registerIcon(
            $iconKey,
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:rsmoembed/Resources/Public/Icons/Backend/' . $iconKey . '.svg']
        );
        $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$iconKey] = $iconKey;
    }

})();
