<?php

declare(strict_types=1);

/**
 * tubee
 *
 * @copyright   Copryright (c) 2017-2019 gyselroth GmbH (https://gyselroth.com)
 * @license     GPL-3.0 https://opensource.org/licenses/GPL-3.0
 */

namespace Tubee\Endpoint\SqlSrvUsers;

use PHPStan\Type\ResourceType;
use Psr\Log\LoggerInterface;
use Tubee\Endpoint\EndpointInterface;

class Wrapper
{
    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Host.
     *
     * @var string
     */
    protected $host;

    /**
     * Username.
     *
     * @var string
     */
    protected $username;

    /**
     * Password.
     *
     * @var string
     */
    protected $passwd;

    /**
     * dbname.
     *
     * @var string
     */
    protected $dbname;

    /**
     * Port.
     *
     * @var int
     */
    protected $port = 1433;

    /**
     * Resource.
     *
     * @var resource
     */
    protected $resource;

    /**
     * Charset.
     *
     * @var string
     */
    protected $charset = 'utf8';

    /**
     * construct.
     */
    public function __construct(string $host, LoggerInterface $logger, string $dbname = null, ?string $username = null, ?string $passwd = null, int $port = 1433)
    {
        $this->logger = $logger;
        $this->host = $host;
        $this->username = $username;
        $this->passwd = $passwd;
        $this->dbname = $dbname;
        $this->port = $port;
    }

    /**
     * Setup.
     */
    public function initialize(): Wrapper
    {
        $this->resource = sqlsrv_connect($this->host.', '.$this->port, ['Database' => $this->dbname ?? '', 'UID' => $this->username ?? '', 'PWD' => $this->passwd ?? '']);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function close(bool $simulate = false): Wrapper
    {
        sqlsrv_close($this->resource);

        return $this;
    }

    /**
     * query.
     */
    public function query(string $query): void
    {
        $this->logger->debug('execute sqlsrv query ['.$query.']', [
            'category' => get_class($this)
        ]);

        $result = sqlsrv_query($this->resource, $query);

        if (!$result) {
            throw new Exception\InvalidQuery('failed to execute sqlsrv query with error '.sqlsrv_errors()[0]['message']);
        }
    }

    /**
     * Prepare query.
     */
    public function prepareValues(string $query, array $values)
    {
        $this->logger->debug('prepare and execute sqlsrv query ['.$query.'] with values [{values}]', [
            'category' => get_class($this),
            'values' => $values,
        ]);

        $stmt = sqlsrv_prepare($this->resource, $query, $values);

        if (!$stmt) {
            throw new Exception\InvalidQuery('failed to prepare sqlsrv query with error '.sqlsrv_errors()[0]['message']);
        }

        if (!sqlsrv_execute($stmt)) {
            throw new Exception\InvalidQuery('failed to execute prepared sqlsrv query with error '.sqlsrv_errors()[0]['message']);
        }

        return $stmt;
    }

    public function getQueryResult($query): array
    {
        $return = [];

        while($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
            $return[] = $row;
        }

        return $return;
    }
}
