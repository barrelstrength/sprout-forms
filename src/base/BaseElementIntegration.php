<?php

namespace barrelstrength\sproutforms\base;

use Craft;

/**
 * Class ElementIntegration
 *
 * @package Craft
 */
abstract class BaseElementIntegration extends ApiIntegration
{
    public function getDefaultAttributes()
    {
        return [];
    }
}

