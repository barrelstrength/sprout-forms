<?php

namespace barrelstrength\sproutforms\integrations\sproutimport\fields;

use barrelstrength\sproutbase\app\import\base\FieldImporter;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutforms\elements\Entry as EntryElement;

class Entries extends FieldImporter
{
    /**
     * @inheritdoc
     */
    public function getModelName()
    {
        return EntryElement::class;
    }

    /**
     * @inheritdoc
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
                'formId' => $groupIds
            ];
        }

        $element = new EntryElement();

        $elementIds = SproutBase::$app->fieldImporter->getMockRelations($element, $attributes, $limit);

        return $elementIds;
    }
}
