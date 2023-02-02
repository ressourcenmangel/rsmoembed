<?php
defined('TYPO3') || die();

call_user_func(function () {
    $GLOBALS['TCA']['sys_file_reference']['palettes']['rsmoembed_title_alt__crop']['showitem'] = 'title,alternative,--linebreak--,crop';
});
