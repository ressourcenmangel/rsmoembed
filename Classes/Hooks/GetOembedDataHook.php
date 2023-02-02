<?php

namespace Ressourcenmangel\Rsmoembed\Hooks;


use Embed\Embed;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;


class GetOembedDataHook
{


    /**
     * Called before saving data to database
     *
     * @param string $status
     * @param string $table
     * @param int $id
     * @param array $fieldArray
     * @param DataHandler $dataHandler
     * @throws \Exception
     */
    public function processDatamap_postProcessFieldArray(
        string      $status,
        string      $table,
                    $id,
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
                //$info = $embed->get('https://www.instagram.com/p/CoDZMlQMIrd/');

                //$info = $embed->get('https://www.facebook.com/Ressourcenmangel/posts/pfbid036kBZ29byhKFuxZqNnDeUwmsqVefuh8wEK2eidES4ExQ6PrqyGsTM7JjdfUw2Lvw5l');

                //$info = $embed->get('https://soundcloud.com/user-898723330/manuchao-kingo-of-the-bongo');

                $info = $embed->get($uriSanitized);

                $infoAll = [];
                $infoAll += [
                    'meta_oembed_title' => $info->getMetas()->get('og:title') ?? '',
                    'meta_oembed_description' => $info->getMetas()->get('og:description') ?? '',
                    'meta_oembed_image' => $info->getMetas()->get('og:image') ?? '',
                    'meta_oembed_url' => $info->getMetas()->get('og:url') ?? '',
                ];

                $infoAll += [
                    //The page title
                    'info_title' => $info->title,
                    //The page description
                    'info_description' => $info->description,
                    //The canonical url
                    'info_url' => $info->url,
                    //The page keywords
                    'info_keywords' => $info->keywords,
                    //The thumbnail or main image
                    'info_image' => $info->image,
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

                $flashMessage = new FlashMessage(
                    'URL is not valid: ' . $uri,
                    'Use valid Url',
                    FlashMessage::ERROR
                );
                $this->flashMessages($flashMessage);
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

            if (intval($id) > 0 ) {
                $contentElement = BackendUtility::getRecord(
                    'tt_content',
                    (int)$id
                );
                // image download agreed
                if ((int) $contentElement['tx_rsmoembed_image_download'] === 1) {
                    $return = $contentElement['tx_rsmoembed_data'];
                    $result = json_decode($return, true);

                    // meta_oembed_image || info_image


                    if ((int)$contentElement['tx_rsmoembed_image'] < 1) {
                        $previewPathReturn = '';
                        if ($result['info_provider_name'] && $result['info_image']) {
                            $previewPathReturn = $this->addFileFromUri($result['info_provider_name'], $result['info_image']);
                            $flashMessage = new FlashMessage(
                                'Preview generated from: ' . $result['info_image'],
                                'New Preview Image',
                                FlashMessage::OK
                            );
                            $this->flashMessages($flashMessage);

                        } elseif ($result['info_provider_name']  && $result['meta_oembed_image']) {
                            // TODO ... oembed_provider_name + oembed_thumbnail_url
                            $previewPathReturn = $this->addFileFromUri($result['oembed_provider_name'], $result['meta_oembed_image']);
                            $flashMessage = new FlashMessage(
                                'Preview generated from: ' . $result['meta_oembed_image'],
                                'New Preview Image',
                                FlashMessage::OK
                            );
                            $this->flashMessages($flashMessage);
                        } else {
                            $flashMessage = new FlashMessage(
                                'No preview image url given.',
                                'Preview Image',
                                FlashMessage::WARNING
                            );
                            $this->flashMessages($flashMessage);

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
                            $newId = 'NEW1234';
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
                                'tx_rsmoembed_image' => $newId,
                                'tx_rsmoembed_image_download' => '',
                                'pid' => $contentElement['pid'],
                            ];

                            $this->processData($data);
                        }
                    } elseif (
                        (int) $fieldArray['tx_rsmoembed_image'] == 0
                        && array_key_exists('tx_rsmoembed_url',$fieldArray)
                    ) {
                        $data['tt_content'][$contentElement['uid']] = [
                            'tx_rsmoembed_image_download' => '',
                            'pid' => $contentElement['pid'],
                        ];
                        $this->processData($data);

                        $flashMessage = new FlashMessage(
                            'No new preview image assigned,
                            to automatically assign a new preview remove the existing and save again.',
                            'Check Preview Image',
                            FlashMessage::WARNING
                        );
                        $this->flashMessages($flashMessage);
                    } elseif (
                        (int)$fieldArray['tx_rsmoembed_image'] == 0
                        && !array_key_exists('tx_rsmoembed_url',$fieldArray)
                    ) {
                        $data['tt_content'][$contentElement['uid']] = [
                            'tx_rsmoembed_image_download' => '',
                            'pid' => $contentElement['pid'],
                        ];
                        $this->processData($data);
                    } else {
                        $data['tt_content'][$contentElement['uid']] = [
                            'tx_rsmoembed_image_download' => '',
                            'pid' => $contentElement['pid'],
                        ];
                        $this->processData($data);
                        $flashMessage = new FlashMessage(
                            'Please check the preview image.',
                            'Check Preview Image',
                            FlashMessage::INFO
                        );
                        $this->flashMessages($flashMessage);
                    }
                }

                /// end of image assign

            }


        }
    }

    /**
     * @param array $data
     */
    private function processData(array $data)
    {
// Get an instance of the DataHandler and process the data
        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();
        // Error or success reporting
        if (count($dataHandler->errorLog) === 0) {
            // Handle success
        } else {
            // Handle errors
        }
    }
    private function addFileFromUri($provider, $thumbnail_url)
    {
        // some providers deliver cruel thumbnail urls with get vars
        $thumbnailUrlWithoutQueryParams = strtok($thumbnail_url,'?');

        $filename = strtolower($provider)
            . '_' . sha1($thumbnail_url);
            //. '_' . basename($thumbnailUrlWithoutQueryParams);

        $previewPathRelative = GeneralUtility::getFileAbsFileName('fileadmin/') . 'o_embed/' . $provider . '/';
        GeneralUtility::mkdir_deep($previewPathRelative);
        $previewPathLocal = GeneralUtility::getFileAbsFileName($previewPathRelative) . $filename;
        if (!pathinfo($previewPathLocal,  PATHINFO_EXTENSION )) {
            $previewPathLocal = $previewPathLocal . '.jpg';
        }

        $previewPathReturn = is_readable($previewPathLocal) ? $previewPathLocal : false;
        if (!$previewPathReturn) {
            $previewImageString = @file_get_contents($thumbnail_url);

            if ($previewImageString) {
                file_put_contents($previewPathLocal, $previewImageString);
            }

            $previewPathReturn = is_readable($previewPathLocal) ? $previewPathLocal : false;

        }
        return $previewPathReturn;
    }

    /**
     * @param FlashMessage $flashMessage
     * @throws \Exception
     * @return mixed
     */
    private function flashMessages(FlashMessage $flashMessage)
    {
        try {
            /** @var FlashMessageService $flashMessageService */
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            /** @var FlashMessageQueue $defaultFlashMessageQueue */
            $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $defaultFlashMessageQueue->enqueue($flashMessage);
        } catch (\Exception $e) {
            // Top level catch to ensure useful following exception handling, because FAL throws top level exceptions.
            // TYPO3\CMS\Core\Database\ReferenceIndex::getRelations() will check the return value of this hook with is_array()
            // so we return false to tell getRelations() to do nothing.
            return false;
        }
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

    /**
     * Simple debug logger, maybe helpful if you have trouble
     * Why do you need this: Belly :-)  Got me.... or not
     * It can write parallel desired log infos, while having a valid JSON response for example
     *
     * This function should write a human-readable file to:
     * typo3temp/FOLDER_SEE_BELOW/timeStamp . _debug . $fileSuffix . txt
     * @usage $this->writeDebugFile($your_content, '_some_file_suffix');
     * @param mixed|null $content The content to debug
     * @param string $fileSuffix The debug file suffix
     */
    private function writeDebugFile($content, string $fileSuffix = 'default'): void
    {
        $debugFolder = '/typo3temp/simplereference_debug/';
        // Secure folder, may contain sensible data
        if (!@is_file(Environment::getPublicPath() . $debugFolder . '.htaccess')) {
            GeneralUtility::writeFileToTypo3tempDir(
                Environment::getPublicPath() . $debugFolder . '.htaccess',
                '
# Apache < 2.3
<IfModule !mod_authz_core.c>
	Order allow,deny
	Deny from all
	Satisfy All
</IfModule>

# Apache >= 2.3
<IfModule mod_authz_core.c>
	Require all denied
</IfModule>
'
            );
        }

        $debugFile = $debugFolder . microtime(true) . '_debug_' . $fileSuffix . '.txt';
        if (!@is_file(Environment::getPublicPath() . $debugFile)) {
            GeneralUtility::writeFileToTypo3tempDir(
                Environment::getPublicPath() . $debugFile,
                print_r($content, true)
            );
        }
    }
}
