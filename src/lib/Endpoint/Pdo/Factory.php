<?php

declare(strict_types=1);

/**
 * tubee.io
 *
 * @copyright   Copryright (c) 2017-2018 gyselroth GmbH (https://gyselroth.com)
 * @license     GPL-3.0 https://opensource.org/licenses/GPL-3.0
 */

namespace Tubee\Endpoint\Pdo;

use Psr\Log\LoggerInterface;
use Tubee\DataType\DataTypeInterface;
use Tubee\Endpoint\EndpointInterface;
use Tubee\Endpoint\Pdo as PdoEndpoint;
use Tubee\Workflow\Factory as WorkflowFactory;

class Factory
{
    /**
     * Build instance.
     */
    public static function build(array $resource, DataTypeInterface $datatype, WorkflowFactory $workflow, LoggerInterface $logger): EndpointInterface
    {
        $options = array_merge([
            'dsn' => null,
            'username' => null,
            'passwd' => null,
            'options' => null,
        ], $resource['resource']);

        $wrapper = new Wrapper($options['dsn'], $logger, $options['username'], $options['passwd'], $options['options']);

        return new PdoEndpoint($resource['name'], $resource['type'], $resource['table'], $wrapper, $datatype, $workflow, $logger, $resource);
    }
}