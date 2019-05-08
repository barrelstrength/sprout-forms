<?php

namespace barrelstrength\sproutforms\base;

use Craft;

/**
 * Class ElementIntegration
 *
 * @package Craft
 *
 * @property array $defaultAttributes
 * @property array $defaultElementFieldsAsOptions
 */
abstract class ElementIntegration extends Integration
{
    public $authorId;

    public $enableSetAuthorToLoggedInUser = false;

    /**
     * Default attributes as options
     *
     * @return array
     */
    public function getDefaultAttributes(): array
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
    public function getDefaultElementFieldsAsOptions(): array
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
    public function getElementFieldsAsOptions($elementGroupId): array
    {
        return [];
    }
}

