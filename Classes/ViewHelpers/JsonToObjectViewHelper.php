<?php
namespace Ressourcenmangel\Rsmoembed\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * JsonToObjectViewHelper
 *
 */
class JsonToObjectViewHelper extends AbstractViewHelper
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
            'The incoming data to convert, or null if VH children should be used'
        );
    }

    /**
     * Applies json_decode() on the specified value.
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @see https://www.php.net/manual/function.json-decode.php
     * @return object
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $value = $renderChildrenClosure();
        return json_decode($value, true);
    }
}
