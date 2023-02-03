<?php
declare(strict_types = 1);
namespace Ressourcenmangel\Rsmoembed\Helper;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class Helper
{
    /**
     * extKey
     *
     * @var string

     */
    protected $extKey = 'rsmoembed';

    /**
     * @param array $data
     * @param string $template
     * @return string
     */
    public function renderContent(array $data, string $template = ''): string
    {
        if (!$template) {
            return 'No Template given';
        }
        // prepare own template
        $fluidTemplateFile = GeneralUtility::getFileAbsFileName(
            'EXT:' . $this->extKey . '/Resources/Private/Oembed/Templates/' . $template
        );
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename($fluidTemplateFile);
        $view->setPartialRootPaths(
            ['EXT:' . $this->extKey . '/Resources/Private/Oembed/Partials/']
        );
        $view->assignMultiple([
            'data' => $data,
            'debug' => $GLOBALS['TYPO3_CONF_VARS']['BE']['debug']
        ]);
        return $view->render();
    }

    /**
     * @param string $html
     * @param string $tag
     * @param array|null $allowedAttribs
     * @return string
     */
    public function extractAndSanitzeIframe (string $html, string $tag = 'iframe', array $allowedAttribs = null) {
        $allowedAttribs = $allowedAttribs ?? [
            'src',
            'width',
            'height',
            'title',
            'allow',
            'name',
            'referrerpolicy',
            'sandbox',
            'scrolling',
            'frameborder',
        ];

        $htmlSanitized = '';
        $result = [];

        $dom = new \DOMDocument;
        $dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->query('//' . $tag . '//@*');

        foreach ($nodes as $node) {
            if (in_array($node->nodeName, $allowedAttribs)) {
                $result[] = $node->nodeName .'="'. htmlspecialchars($node->nodeValue) . '"';
            }
        }

        if (count($result)) {
            $htmlSanitized = '<iframe ' . implode(' ', $result) . '></iframe>';
        }
        return $htmlSanitized;
    }
}
