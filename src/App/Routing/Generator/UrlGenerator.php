<?php
namespace App\Routing\Generator;

use Symfony\Component\Routing\Generator\UrlGenerator as BaseUrlGenerator;

/**
 * Specific UrlGenerator to manage simplified route generation based on entities
 * @package App\Routing\Generator
 */
class UrlGenerator extends BaseUrlGenerator
{
    /**
     * {@inheritDoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        // if $parameters is a entity with getRouteParams method
        if(is_object($parameters) && method_exists($parameters, 'getRouteParameters')) {
            $parameters = $parameters->getRouteParameters();
        }
        return parent::generate($name, $parameters, $referenceType);
    }
}
