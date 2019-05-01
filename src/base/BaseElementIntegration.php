<?php

namespace barrelstrength\sproutforms\base;

use Craft;
use craft\elements\User;

/**
 * Class ElementIntegration
 *
 * @package Craft
 */
abstract class BaseElementIntegration extends Integration
{
    public $authorId;

    public $enableSetAuthorToLoggedInUser = false;

    /**
     * Default attributes as options
     *
     * @return array
     */
    public function getDefaultAttributes()
    {
        return [
            [
                'label' => Craft::t('app', 'Title'),
                'value' => 'title'
            ]
        ];
    }

    /**
     * @return array
     */
    public function getDefaultElementFieldsAsOptions()
    {
        $options = [];

        if ($this->getDefaultAttributes()) {
            foreach ($this->getDefaultAttributes() as $item) {
                $options[] = $item;
            }
        }

        return $options;
    }

    /**
     * @param $elementGroupId
     *
     * @return array
     */
    public function getElementFieldsAsOptions($elementGroupId)
    {
        return [];
    }
}

