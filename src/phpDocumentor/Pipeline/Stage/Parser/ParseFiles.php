<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Pipeline\Stage\Parser;

use phpDocumentor\Parser\Middleware\ReEncodingMiddleware;
use phpDocumentor\Parser\Parser;
use Psr\Log\LoggerInterface;

final class ParseFiles
{
    private Parser $parser;
    private LoggerInterface $logger;
    private ReEncodingMiddleware $reEncodingMiddleware;

    public function __construct(
        Parser $parser,
        LoggerInterface $logger,
        ReEncodingMiddleware $reEncodingMiddleware
    ) {
        $this->parser = $parser;
        $this->logger = $logger;
        $this->reEncodingMiddleware = $reEncodingMiddleware;
    }

    public function __invoke(ApiSetPayload $payload): ApiSetPayload
    {
        $apiConfig = $payload->getSpecification();

        $builder = $payload->getBuilder();
        $builder->setApiSpecification($apiConfig);

        $encoding = $apiConfig['encoding'] ?? '';
        if ($encoding) {
            $this->reEncodingMiddleware->withEncoding($encoding);
        }

        $this->parser->setMarkers($apiConfig['markers'] ?? []);
        $this->parser->setValidate(($apiConfig['validate'] ?? 'false') === 'true');
        $this->parser->setDefaultPackageName($apiConfig['default-package-name'] ?? '');

        $this->logger->notice('Parsing files');
        $project = $this->parser->parse($payload->getFiles());

        $payload->getBuilder()->createApiDocumentationSet($project);

        return $payload;
    }
}
