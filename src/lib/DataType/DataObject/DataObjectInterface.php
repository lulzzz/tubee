<?php

declare(strict_types=1);

/**
 * tubee.io
 *
 * @copyright   Copryright (c) 2017-2018 gyselroth GmbH (https://gyselroth.com)
 * @license     GPL-3.0 https://opensource.org/licenses/GPL-3.0
 */

namespace Tubee\DataType\DataObject;

use MongoDB\BSON\UTCDateTime;
use Tubee\DataType\DataTypeInterface;
use Tubee\Resource\ResourceInterface;

interface DataObjectInterface extends ResourceInterface
{
    /**
     * Get object version.
     */
    public function getVersion(): int;

    /**
     * Get deleted timestamp.
     */
    public function getDeleted(): ?UTCDateTime;

    /**
     * Get changed timestamp.
     */
    public function getChanged(): ?UTCDateTime;

    /**
     *Get created timestamp.
     */
    public function getCreated(): UTCDateTime;

    /**
     * Get data.
     */
    public function getData(): array;

    /**
     * Get data type.
     */
    public function getDataType(): DataTypeInterface;

    /**
     * Get endpoints.
     */
    public function getEndpoints(): array;
}