<?php

namespace barrelstrength\sproutforms\base;

use Craft;

/**
 * Class ElementIntegration
 *
 * @package Craft
 */
abstract class ElementIntegration extends Integration
{
    // Any general customizations we need specifically for Element Integrations

    // We may want to consider extending the Form Field API and adding support for Form Fields to identify what Field Class/Classes they can be mapped to. In the Element Integration case, this method resolves the field mapping by matching Sprout Form fields classes to Craft Field classes.
//    public function resolveFieldMapping() {}
}

