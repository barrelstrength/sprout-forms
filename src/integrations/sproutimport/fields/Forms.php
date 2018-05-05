<?php

namespace barrelstrength\sproutforms\integrations\sproutimport\fields;

use barrelstrength\sproutbase\sproutimport\contracts\BaseFieldImporter;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutimport\SproutImport;

class Forms extends BaseFieldImporter
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
     */
    public function getMockData()
    {
        $settings = $this->model->settings;
        $limit = SproutImport::$app->fieldImporter->getLimit($settings['limit'], 1);
        $sources = $settings['sources'];
        $attributes = [];

        $groupIds = SproutImport::$app->fieldImporter->getElementGroupIds($sources);

        if (!empty($groupIds) && $groupIds != '*') {
            $attributes = [
                'groupId' => $groupIds
            ];
        }

        $element = new FormElement();

        $elementIds = SproutImport::$app->fieldImporter->getMockRelations($element, $attributes, $limit);

        return $elementIds;
    }
}
