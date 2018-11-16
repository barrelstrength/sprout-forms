<?php

namespace barrelstrength\sproutforms\integrations\sproutimport\fields;

use barrelstrength\sproutbase\app\import\base\FieldImporter;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutforms\elements\Form as FormElement;

class Forms extends FieldImporter
{
    /**
     * @inheritdoc
     */
    public function getModelName()
    {
        return FormElement::class;
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function getMockData()
    {
        $settings = $this->model->settings;
        $limit = SproutBase::$app->fieldImporter->getLimit($settings['limit'], 1);
        $sources = $settings['sources'];
        $attributes = [];

        $groupIds = SproutBase::$app->fieldImporter->getElementGroupIds($sources);

        if (!empty($groupIds) && $groupIds != '*') {
            $attributes = [
                'groupId' => $groupIds
            ];
        }

        $element = new FormElement();

        $elementIds = SproutBase::$app->fieldImporter->getMockRelations($element, $attributes, $limit);

        return $elementIds;
    }
}
