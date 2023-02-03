<?php

namespace Ressourcenmangel\Rsmoembed\ViewHelpers;

use Ressourcenmangel\Rsmoembed\Helper\Helper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * JsonToObjectViewHelper
 *
 */
class ExtractAndSanitizeTagViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument(
            'value',
            'string',
            'The incoming html convert, or null if VH children should be used'
        );
        $this->registerArgument(
            'tag',
            'string',
            'The tag to keep, only the first found tag is kept'
        );
        $this->registerArgument(
            'attributes',
            'array',
            'The attributed to keep'
        );
    }

    /**
     * Applies a sanitize on the specified value.
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return object
     * @see https://www.php.net/manual/function.json-decode.php
     */
    public static function renderStatic(
        array                     $arguments,
        \Closure                  $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    )
    {
        $value = $renderChildrenClosure();

        $helper = GeneralUtility::makeInstance(Helper::class);

        if ($value) {
            $value = $helper->extractAndSanitzeIframe($value);
        }
        return $value;
    }
}
