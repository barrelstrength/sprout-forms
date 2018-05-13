<?php

namespace barrelstrength\sproutforms\integrations\sproutimport\fields;

use barrelstrength\sproutbase\app\import\base\FieldImporter;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutimport\SproutImport;

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
                'formId' => $groupIds
            ];
        }

        $element = new EntryElement();

        $elementIds = SproutImport::$app->fieldImporter->getMockRelations($element, $attributes, $limit);

        return $elementIds;
    }
}
