<?php
declare(strict_types = 1);
namespace Ressourcenmangel\Rsmoembed\Form\Element;

use Ressourcenmangel\Rsmoembed\Helper\Helper;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class RsmOembedData extends AbstractFormElement
{
    /**
     * extKey
     *
     * @var string

     */
    protected $extKey = 'rsmoembed';

    public function render(): array
    {
        // Custom TCA properties and other data can be found in $this->data, for example the above
        // parameters are available in $this->data['parameterArray']['fieldConf']['config']['parameters']
        $helper = GeneralUtility::makeInstance(Helper::class);
        $result['html'] = $helper->renderContent(
            $this->data['databaseRow'],
            'RsmOembedData.html'
        );
        return $result;
    }
}
