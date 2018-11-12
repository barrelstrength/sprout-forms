<?php

namespace barrelstrength\sproutforms\base;

use Craft;

/**
 * Class ApiIntegration
 *
 * @package Craft
 */
abstract class ApiIntegration extends Integration
{
    // Any general customizations we need specifically for API Integrations

    // In the API Integration case, this method resolves the field mapping by matching Sprout Form fields classes to fields available at the EndPoint.
//    public function resolveFieldMapping() {}
}

