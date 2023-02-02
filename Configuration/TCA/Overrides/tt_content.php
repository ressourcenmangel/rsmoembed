<?php
defined('TYPO3') || die();

call_user_func(function () {
    $tmp_rsmoembed_extkey = 'rsmoembed';
    $tmp_rsmoembed_lang_prefix = 'LLL:EXT:' . $tmp_rsmoembed_extkey . '/Resources/Private/Language/locallang_db.xlf:';
    $tmp_rsmoembed_columns = [

        'tx_rsmoembed_url' => [
            'exclude' => true,
            'label' => 'LLL:EXT:rsmoembed/Resources/Private/Language/locallang_db.xlf:tt_content.tx_rsmoembed_url',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim,required',
                'default' => ''
            ],
        ],
        'tx_rsmoembed_data' => [
            'exclude' => true,
            'label' => 'LLL:EXT:rsmoembed/Resources/Private/Language/locallang_db.xlf:tt_content.tx_rsmoembed_data',
            'config' => [
                'type' => 'text',
                'cols' => 100,
                'rows' => 5,
                'eval' => 'trim',
                'default' => ''
            ]
        ],
        'tx_rsmoembed_image_download' => [
            'exclude' => true,
            'label' => 'LLL:EXT:rsmoembed/Resources/Private/Language/locallang_db.xlf:tt_content.tx_rsmoembed_image_download',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        0 => 'LLL:EXT:rsmoembed/Resources/Private/Language/locallang_db.xlf:tt_content.tx_rsmoembed_image_download.0',
                        1 => '',
                    ]
                ],
                'default' => 0,
            ]
        ],
        'tx_rsmoembed_image' => [
            'exclude' => true,
            'label' => 'LLL:EXT:rsmoembed/Resources/Private/Language/locallang_db.xlf:tt_content.tx_rsmoembed_image',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'tx_rsmoembed_image',
                [
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference'
                    ],
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                            --palette--;;rsmoembed_title_alt__crop,
                            --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                'showitem' => '
                            --palette--;;rsmoembed_title_alt__crop,
                            --palette--;;filePalette'
                            ],
                        ],
                    ],
                    'foreign_match_fields' => [
                        'fieldname' => 'tx_rsmoembed_image',
                        'tablenames' => 'tt_content',
                        'table_local' => 'sys_file',
                    ],
                    'maxitems' => 1,
                    'minitems' => 0
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            ),
        ],
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
        'tt_content',
        $tmp_rsmoembed_columns
    );


//////////////////////////////////////////////////////////////
// CE Oembed Default Default
//////////////////////////////////////////////////////////////
    $tmp_rsmoembed_ce = $tmp_rsmoembed_extkey . '_default';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
        'tt_content',
        'CType',
        [
            'LLL:EXT:' . $tmp_rsmoembed_extkey . '/Resources/Private/Language/RsmoembedDefault/locallang_db.xlf:title',
            $tmp_rsmoembed_ce,
            $tmp_rsmoembed_ce,
        ],
        'text',
        'after'
    );
//tx_rsmoembed_data,
    $GLOBALS['TCA']['tt_content']['types'][$tmp_rsmoembed_ce] = [
        'showitem' => '
    --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
    --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.headers;headers,
        bodytext,
        tx_rsmoembed_url,

        tx_rsmoembed_image_download,
        tx_rsmoembed_image,
    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
        --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,
        --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,
    --div--;LLL:EXT:sitedefault/Resources/Private/Language/locallang_db.xlf:div.transitions, tx_sitedefault_flex_b,,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
        --palette--;;language,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
        --palette--;;hidden,
        --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories, categories,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes, rowDescription,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended'
        ,
        'columnsOverrides' => [
            'bodytext' => [
                'config' => [
                    'softref' => 'typolink_tag,images,email[subst],url',
                    'enableRichtext' => true,
                ],
            ],
        ],
    ];
});
