<?php

namespace barrelstrength\sproutforms\base;

use Craft;
use craft\base\Model;

/**
 * Class IntegrationType
 *
 * @package Craft
 */
abstract class Integration extends Model
{
    /**
     * Name of the Integration
     *
     * @return mixed
     */
    abstract public function getName();

    /**
     * Return Class name as Type
     *
     * @return string
     */
    abstract public function getType();

    /**
     * Send the submission to the desired endpoint
     */
    abstract public function submit();

    /**
     * Settings that help us customize the Field Mapping Table
     *
     * Each settings template will also call a Twig Field Mapping Table Macro to help with the field mapping (can we just have a Twig Macro that wraps the default Craft Table for this and outputs two columns?)
     */
    public function getSettingsHtml() {}

    /**
     * Process the submission and field mapping settings to get the payload. Resolve the field mapping.
     *
     * @param $fields
     *
     * @return mixed
     */
    public function resolveFieldMapping($fields) {
        return $fields;
    }
}

