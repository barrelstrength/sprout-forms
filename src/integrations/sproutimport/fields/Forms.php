<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\integrations\sproutimport\fields;

use barrelstrength\sproutbaseimport\base\FieldImporter;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use barrelstrength\sproutforms\elements\Form as FormElement;
use Exception;

class Forms extends FieldImporter
{
    /**
     * @inheritdoc
     */
    public function getModelName(): string
    {
        return FormElement::class;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function getMockData()
    {
        $settings = $this->model->settings;
        $limit = SproutBaseImport::$app->fieldImporter->getLimit($settings['limit'], 1);
        $sources = $settings['sources'];
        $attributes = [];

        $groupIds = SproutBaseImport::$app->fieldImporter->getElementGroupIds($sources);

        if (!empty($groupIds) && $groupIds != '*') {
            $attributes = [
                'groupId' => $groupIds
            ];
        }

        $element = new FormElement();

        $elementIds = SproutBaseImport::$app->fieldImporter->getMockRelations($element, $attributes, $limit);

        return $elementIds;
    }
}
