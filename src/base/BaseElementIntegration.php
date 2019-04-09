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
    public function getDefaultEntryFieldsAsOptions()
    {
        $options = [[
            'label' => Craft::t('sprout-forms', 'None'),
            'value' => ''
        ]];

        if ($this->getDefaultAttributes()){
            foreach ($this->getDefaultAttributes() as $item) {
                $options[] = $item;
            }
        }

        return $options;
    }
}

