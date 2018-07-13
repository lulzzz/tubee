<?php

declare(strict_types=1);

/**
 * tubee.io
 *
 * @copyright   Copryright (c) 2017-2018 gyselroth GmbH (https://gyselroth.com)
 * @license     GPL-3.0 https://opensource.org/licenses/GPL-3.0
 */

namespace Tubee\Resource;

use Closure;
use Psr\Http\Message\ServerRequestInterface;

class AttributeResolver
{
    /**
     * Resolve.
     */
    public static function resolve(ServerRequestInterface $request, ResourceInterface $resource, array $resolvable): array
    {
        $params = $request->getQueryParams();
        $attributes = [];

        if (isset($params['attributes'])) {
            $attributes = $params['attributes'];
        }

        if (0 === count($attributes)) {
            return self::translateAttributes($resolvable, $resource);
        }

        return self::translateAttributes($resolvable, array_intersect_key($resolvable, array_flip($attributes)));
    }

    /**
     * Execute closures.
     */
    protected static function translateAttributes(array $resolvable, ResourceInterface $resource): array
    {
        foreach ($resolvable as $key => &$value) {
            if ($value instanceof Closure) {
                $result = $value($resource);
                if (null === $result) {
                    unset($resolvable[$key]);
                } else {
                    $value = $result;
                }
            } elseif ($value === null) {
                unset($resolvable[$key]);
            }
        }

        return $resolvable;
    }
}