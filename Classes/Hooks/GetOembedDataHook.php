<?php

namespace Ressourcenmangel\Rsmoembed\Hooks;


use Embed\Embed;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;

class GetOembedDataHook
{
    /**
     * The prefix content element CType
     */
    private $cType = 'rsmoembed_default';

    /**
     * Called before saving data to database
     *
     * @param string $status
     * @param string $table
     * @param mixed $id
     * @param array $fieldArray
     * @param DataHandler $dataHandler
     * @throws \Exception
     */
    public function processDatamap_postProcessFieldArray(
        string      $status,
        string      $table,
        mixed       $id,
        array       &$fieldArray,
        DataHandler &$dataHandler
    ): void
    {
        if ($table === 'tt_content') {
            $uri = $fieldArray['tx_rsmoembed_url'] ?? false;

            $uriSanitized = filter_var($uri, FILTER_SANITIZE_URL);

            // if we have a valid url...
            if (filter_var($uriSanitized, FILTER_VALIDATE_URL) &&
                (parse_url($uriSanitized, PHP_URL_SCHEME) === 'http' ||
                    parse_url($uriSanitized, PHP_URL_SCHEME) === 'https')
            ) {
                $embed = GeneralUtility::makeInstance(Embed::class);

                $embed->setSettings([
                    //'oembed:query_parameters' => [],  //Extra parameters send to oembed
                    //'twitch:parent' => 'example.com', //Required to embed twitch videos as iframe
                    //'facebook:token' => '1234|5678',  //Required to embed content from Facebook
                    //'instagram:token' => '1234|5678', //Required to embed content from Instagram
                    //'twitter:token' => 'asdf',        //Improve the data from twitter
                ]);

                $info = $embed->get($uriSanitized);

                $infoAll = [];
                $infoOembed = [
                    'meta_oembed_title' => $info->getMetas()->str('og:title') ?? '',
                    'meta_oembed_description' => $info->getMetas()->str('og:description') ?? '',
                    'meta_oembed_url' => $info->getMetas()->str('og:url') ?? '',
                    'meta_oembed_image' => $info->getMetas()->str('og:image') ?? '',
                ];

                $infoAll += [
                    //The page title
                    'info_title' => $infoOembed['meta_oembed_title'] ?? $info->title,
                    //The page description
                    'info_description' => $infoOembed['meta_oembed_description'] ?? $info->description,
                    //The canonical url
                    'info_url' => $infoOembed['meta_oembed_url'] ?? $info->url,
                    //The thumbnail or main image
                    'info_image' => $infoOembed['meta_oembed_image'] ?? $info->image,
                    //The page keywords
                    'info_keywords' => $info->keywords,
                    //The code to embed the image, video, etc
                    'info_code_html' => $info->code ? $info->code->html : '',
                    //The exact width of the embed code (if exists)
                    'info_code_width' => $info->code ? $info->code->width : '',
                    //The exact height of the embed code (if exists)
                    'info_code_height' => $info->code ? $info->code->height : '',
                    //The aspect ratio (width/height)
                    'info_code_ratio' => $info->code ? $info->code->ratio : '',
                    //The resource author
                    'info_author_name' => $info->authorName,
                    //The author url
                    'info_author_url' => $info->authorUrl,
                    //The cms used
                    'info_cms' => $info->cms,
                    //The language of the page
                    'info_language' => $info->language,
                    //The alternative languages
                    'info_languages' => $info->languages,
                    //The provider name of the page (Youtube, etc)
                    'info_provider_name' => $info->providerName,
                    //The provider url
                    'info_provider_url' => $info->providerUrl,
                    //The big icon of the site
                    'info_icon' => $info->icon,
                    //The favicon of the site (an .ico file or a png with up to 32x32px)
                    'info_favicon' => $info->favicon,
                    //The published time of the resource
                    'info_published_time' => $info->publishedTime,
                    //The license url of the resource
                    'info_license' => $info->license,
                    //The RSS/Atom feeds
                    'info_feeds' => $info->feeds,
                ];

                $fieldArray['tx_rsmoembed_data'] = json_encode($infoAll, JSON_PRETTY_PRINT);

            } elseif ($uri) {
                $fieldArray['tx_rsmoembed_url'] = '';
                $fieldArray['tx_rsmoembed_data'] = '';


                $this->addFlashMessage(
                    'URL is not valid: ' . $uri,
                    'Use valid Url',
                    AbstractMessage::ERROR
                );
            }
        }
    }

    /**
     * Post-process after save
     *
     * @param string $status
     * @param string $table
     * @param string $id
     * @param array $fieldArray
     * @param DataHandler $dataHandler
     * @return mixed
     * @throws \Exception
     *
     */
    public function processDatamap_afterDatabaseOperations(
        string      $status,
        string      $table,
                    $id,
        array       $fieldArray,
        DataHandler $dataHandler
    ): void
    {
        if ($table === 'tt_content' && ($status === 'new' || $status === 'update')) {

            if (strpos($id, 'NEW') !== false) {
                // Replace NEW...-ID with real uid:
                $id = $dataHandler->substNEWwithIDs[$id];
            }

            if (intval($id) > 0) {
                $contentElement = BackendUtility::getRecord(
                    'tt_content',
                    (int)$id
                );

                // image download agreed
                if ((int)$contentElement['tx_rsmoembed_image_download'] === 1
                    && $contentElement['CType'] === $this->cType) {
                    $return = $contentElement['tx_rsmoembed_data'];
                    $result = json_decode($return, true);

                    if ((int)$contentElement['tx_rsmoembed_image'] < 1) {
                        $previewPathReturn = '';
                        if ($result['info_provider_name'] && $result['info_image']) {
                            $previewPathReturn = $this->addFileFromUri($result['info_provider_name'], $result['info_image']);

                            $this->addFlashMessage(
                                'Preview generated from: ' . $result['info_image'],
                                'New Preview Image',
                                AbstractMessage::OK
                            );
                        } else {

                            $this->resetImageDownload($contentElement);
                            $this->addFlashMessage(
                                'No preview image url given.',
                                'Preview Image',
                                AbstractMessage::WARNING
                            );
                        }
                        if ($previewPathReturn) {
                            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
                            try {
                                $fileObject = $resourceFactory->retrieveFileOrFolderObject($previewPathReturn);
                            } catch (\Exception $e) {
                                // silent die
                            }

                            $fileObjectUid = $fileObject->getProperty('uid');

                            // Assemble DataHandler data
                            $newId = 'NEW1' . random_int(100000, 999999);
                            $data = [];
                            $data['sys_file_reference'][$newId] = [
                                'table_local' => 'sys_file',
                                'uid_local' => $fileObjectUid,
                                'tablenames' => 'tt_content',
                                'uid_foreign' => $contentElement['uid'],
                                'fieldname' => 'tx_rsmoembed_image',
                                'pid' => $contentElement['pid'],
                            ];

                            $data['tt_content'][$contentElement['uid']] = [
                                'CType' => $this->cType,
                                'tx_rsmoembed_image' => $newId,
                                'tx_rsmoembed_image_download' => '',
                                'pid' => $contentElement['pid'],
                            ];

                            $this->processData($data);
                        }
                    } elseif (
                        isset($fieldArray['tx_rsmoembed_image'])
                        && (int)$fieldArray['tx_rsmoembed_image'] === 0
                        && array_key_exists('tx_rsmoembed_url', $fieldArray)
                    ) {
                        $this->resetImageDownload($contentElement);
                        $this->addFlashMessage(
                            'No new preview image assigned,
                            to automatically assign a new preview remove the existing and save again.',
                            'Check Preview Image',
                            AbstractMessage::WARNING
                        );
                    } elseif (
                        isset($fieldArray['tx_rsmoembed_image'])
                        && (int)$fieldArray['tx_rsmoembed_image'] === 0
                        && !array_key_exists('tx_rsmoembed_url', $fieldArray)
                    ) {
                        $this->resetImageDownload($contentElement);
                    } else {
                        $this->resetImageDownload($contentElement);
                        $this->addFlashMessage(
                            'Please check the preview image.',
                            'Check Preview Image'
                        );
                    }
                }
                /// end of image assign
            }
        }
    }

    /**
     * @param array $contentElement
     */
    private function resetImageDownload(array $contentElement): void
    {
        $data['tt_content'][$contentElement['uid']] = [
            'CType' => $this->cType,
            'tx_rsmoembed_image_download' => '',
            'pid' => $contentElement['pid'],
        ];
        $this->processData($data);
    }

    /**
     * @param array $data
     */
    private function processData(array $data): void
    {
        // Get an instance of the DataHandler and process the data
        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();
//      Error or success reporting
//        if (count($dataHandler->errorLog) === 0) {
//            // Handle success
//        } else {
//            // Handle errors
//        }
    }

    /**
     * @param string $provider
     * @param string $thumbnailUrl
     * @return bool|string
     */
    private function addFileFromUri(string $provider, $thumbnailUrl): bool|string
    {
        // some providers deliver cruel thumbnail urls with get vars
        // $thumbnailUrlWithoutQueryParams = strtok($thumbnailUrl, '?');

        $filename = strtolower($provider)
            . '_' . sha1($thumbnailUrl);
        //. '_' . basename($thumbnailUrlWithoutQueryParams);

        $previewPathRelative = GeneralUtility::getFileAbsFileName('fileadmin/') . 'o_embed/' . $provider . '/';
        GeneralUtility::mkdir_deep($previewPathRelative);
        $previewPathLocal = GeneralUtility::getFileAbsFileName($previewPathRelative) . $filename;
        if (!pathinfo($previewPathLocal, PATHINFO_EXTENSION)) {
            // delivers avif
            // https://www.php.net/manual/de/function.sha1-file.php
            $previewPathLocal = $previewPathLocal . '.jpg';
        }

        $previewPathReturn = is_readable($previewPathLocal) ? $previewPathLocal : false;
        if (!$previewPathReturn) {
            $previewImageString = @file_get_contents($thumbnailUrl);

            if ($previewImageString) {
                file_put_contents($previewPathLocal, $previewImageString);
            }

            $previewPathReturn = is_readable($previewPathLocal) ? $previewPathLocal : false;
        }
        return $previewPathReturn;
    }

    /**
     * @param string $message
     * @param string $title
     * @param int $severity
     * @return void
     * @throws \TYPO3\CMS\Core\Exception
     */
    protected function addFlashMessage(
        string $message,
        string $title = '',
        int    $severity = AbstractMessage::INFO
    ): void
    {
        $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message, $title, $severity, true);
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }

    /**
     * Returns the current BE user.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
