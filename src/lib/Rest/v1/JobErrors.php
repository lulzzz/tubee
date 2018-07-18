<?php

declare(strict_types=1);

/**
 * tubee.io
 *
 * @copyright   Copryright (c) 2017-2018 gyselroth GmbH (https://gyselroth.com)
 * @license     GPL-3.0 https://opensource.org/licenses/GPL-3.0
 */

namespace Tubee\Rest\v1;

use Fig\Http\Message\StatusCodeInterface;
use Lcobucci\ContentNegotiation\UnformattedResponse;
use Micro\Auth\Identity;
use MongoDB\BSON\ObjectId;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tubee\Acl;
use Tubee\Job\Factory as JobFactory;
use Tubee\Mandator\Factory as MandatorFactory;
use Tubee\Rest\Pager;
use Zend\Diactoros\Response;

class JobErrors
{
    /**
     * Init.
     */
    public function __construct(JobFactory $job, Acl $acl, MandatorFactory $mandator)
    {
        $this->job = $job;
        $this->acl = $acl;
        $this->mandator = $mandator;
    }

    /**
     * Get errors.
     */
    public function getAll(ServerRequestInterface $request, Identity $identity, ObjectId $job): ResponseInterface
    {
        $query = array_merge([
            'offset' => 0,
            'limit' => 20,
            'query' => [],
        ], $request->getQueryParams());

        $errors = $this->job->getErrors($job, $query['query'], $query['offset'], $query['limit']);
        $body = $this->acl->filterOutput($request, $identity, $errors);
        $body = Pager::fromRequest($body, $request);

        return new UnformattedResponse(
            (new Response())->withStatus(StatusCodeInterface::STATUS_OK),
            $body,
            ['pretty' => isset($query['pretty'])]
        );
    }

    /**
     * Get errors.
     */
    public function getOne(ServerRequestInterface $request, Identity $identity, ObjectId $job, ObjectId $error): ResponseInterface
    {
        $query = $request->getQueryParams();

        return new UnformattedResponse(
            (new Response())->withStatus(StatusCodeInterface::STATUS_OK),
            $this->job->getError($error)->decorate($request),
            ['pretty' => isset($query['pretty'])]
        );
    }

    /**
     * Watch errors.
     */
    public function watchAll(ServerRequestInterface $request, Identity $identity, ObjectId $job): ResponseInterface
    {
        $cursor = $this->job->watchErrors($job);

        $iterator = function () use ($cursor) {
            $iterator = new \IteratorIterator($cursor);

            $iterator->rewind();

            while (true) {
                if ($iterator->valid()) {
                    $document = $iterator->current();
                    yield $document;
                    //printf("Consumed document created at: %s\n", $document->createdAt);
                }

                $iterator->next();
            }
        };

        $encoder = (new \Violet\StreamingJsonEncoder\BufferJsonEncoder($iterator))
    ->setOptions(JSON_PRETTY_PRINT);

        $stream = new \Violet\StreamingJsonEncoder\JsonStream($encoder);

        while (!$stream->eof()) {
            echo $stream->read(1024 * 8);
        }

        return (new Response($stream))->withStatus(StatusCodeInterface::STATUS_OK);
    }
}
