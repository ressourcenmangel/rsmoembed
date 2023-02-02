<?php

namespace Ressourcenmangel\Rsmoembed\Middleware;

/**
 * Usage:
 * send parameters as _GET or _POST,
 * please see usage of requestMethodIsPost or requestMethodIsGet below
 * Used parameters List:
 *  api[action]       = MANDATORY: Action to do, see $allowedCommands below, too
 *  api[record]       = OPTIONAL: The record, usually an uid
 *
 * Debug:
 *  Simple debug logger, maybe helpful if you have trouble
 *  NEVER use this in production environment
 *  You have to delete debug files on your own.
 *
 *  This function writes a file to:
 *  typo3temp/api/time() . _debug . _some_file_suffix . txt
 * @usage: $this->writeDebugFile($WHAT_TO_DEBUG, '_initializeAndCheckFeUser');
 *
 * */

// @TODO: rework and create documentation
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class Api implements MiddlewareInterface
{
    /**
     * The prefix extension key / folder
     * @var string $extKey
     */
    private $extKey = 'rsmoembed';
    /**
     * The prefix for GET or POST var array
     * used like: ?requestKey[findOne]=123
     * @var string $requestKey
     */
    private $requestKey = 'tx_rsmoembed_api';

    /**
     * The incoming _GET or _POST vars
     * @var array $parameters
     */
    private $parameters = [];

    /**
     * The allowed functions / commands
     * @var array $allowedCommands
     */
    private $allowedCommands = [
        'findOne',
    ];

    /**
     * The main DB table used here
     * @var string $baseTable
     */
    private $baseTable = 'tt_content';

    /**
     * The content
     * @var string $content
     */
    private $content = '';

    /**
     * Main function, returns the final result
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler
    ): ResponseInterface
    {
        /** @var ServerRequestInterface $request */


        $this->parameters = $request->getParsedBody()[$this->requestKey]
            ?? $request->getQueryParams()[$this->requestKey]
            ?? null;



        if (is_array($this->parameters)
            && array_key_exists('action', $this->parameters)) {

            // command is the function to call
            // must be in the array of $allowedCommands
            $command = (string)$this->parameters['action'] ?? '';


            if ($command
                && in_array($command, $this->allowedCommands)
                && method_exists($this, $command)
            ) {
                $this->$command();
            } else {
                $this->content = "<p>Something went wrong. Maybe action: -| $command |- does not exist or is not allowed here.</p>";

            }

            $body = new Stream('php://temp', 'rw');

            $body->write($this->content);

            return (new Response())
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
                ->withHeader('Cache-Control', 'private, no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
                ->withHeader('content-type', 'text/html; charset=utf-8')
                ->withHeader('Pragma', 'no-cache')
                ->withBody($body)
                ->withStatus(200);
        }

        return $handler->handle($request);
    }


    /**
     * Find content element by uid
     * @return void
     */

    private function findOne(): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($this->baseTable);
        $result = $connection
            ->select('*')
            ->from($this->baseTable)
            ->where('uid=:theUid')
            ->andWhere('CType in ("rsmoembed_default")')
            ->setParameters(
                [
                    'theUid' => $this->parameters['uid'],
                ], [
                    \PDO::PARAM_INT,
                ]
            )
            ->execute()
            ->fetchAssociative();

        if (is_array($result)) {
            $this->content = $this->renderContent($result);
        } else {
            $this->content = '';
        }
    }

    /**
     * @param string $templateName
     * @return mixed
     */
    protected function renderContent($data)
    {
        // prepare own template
        $fluidTemplateFile = GeneralUtility::getFileAbsFileName(
            'EXT:' . $this->extKey . '/Resources/Private/Api/Content.html'
        );
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename($fluidTemplateFile);
        $view->assignMultiple([
            'data' => $data,
            'oembedData' => json_decode($data['tx_rsmoembed_data'], true),
        ]);

        return $view->render();
    }


    /**
     * Simple debug logger, maybe helpful if you have trouble
     * This function writes a file to:
     * typo3temp/api/time() . _debug . _some_file_suffix . txt
     * @usage $this->writeDebugFile($your_content, '_some_file_suffix');
     * @param mixed|null $content The content to debug
     * @param string $fileSuffix The debug file suffix
     */
    private function writeDebugFile($content, string $fileSuffix = '1'): void
    {
        $debugFile = 'typo3temp/assets/sitedefault_api_debug/' . time() . '_debug_' . $fileSuffix . '.txt';
        if (!@is_file(Environment::getPublicPath() . '/' . $debugFile)) {
            GeneralUtility::writeFileToTypo3tempDir(
                Environment::getPublicPath() . '/' . $debugFile,
                print_r($content, true)
            );
        }
    }
}
