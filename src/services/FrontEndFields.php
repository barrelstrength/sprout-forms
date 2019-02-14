<?php

namespace barrelstrength\sproutforms\services;

use Craft;
use yii\base\Component;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\Tag;
use craft\models\Section;

/**
 *
 * @property array $singlesEntries
 */
class FrontEndFields extends Component
{
    /**
     * @param array $settings field settings
     *
     * @return array
     */
    public function getFrontEndEntries($settings)
    {
        $entries = [];
        $sectionsService = Craft::$app->getSections();

        if (is_array($settings['sources'])) {
            foreach ($settings['sources'] as $source) {
                $section = explode(':', $source);
                $pos = count($entries) + 1;

                if (count($section) == 2) {
                    $sectionById = $sectionsService->getSectionByUid($section[1]);

                    $entries[$pos]['entries'] = Entry::find()->sectionId($sectionById->id)->all();
                    $entries[$pos]['section'] = $sectionById;
                } else if ($section[0] == 'singles') {
                    $singles = $this->getSinglesEntries();

                    $entries[$pos]['entries'] = $singles;
                    $entries[$pos]['singles'] = true;
                }
            }
        } else if ($settings['sources'] == '*') {
            $sections = $sectionsService->getAllSections();

            foreach ($sections as $section) {
                $pos = count($entries) + 1;

                if ($section->type != Section::TYPE_SINGLE) {
                    $sectionById = $sectionsService->getSectionById($section->id);

                    $entries[$pos]['entries'] = Entry::find()->sectionId($section->id)->all();
                    $entries[$pos]['section'] = $sectionById;
                }
            }

            $singles = $this->getSinglesEntries();
            $pos = count($entries) + 1;
            $entries[$pos]['entries'] = $singles;
            $entries[$pos]['singles'] = true;
        }

        return $entries;
    }

    /**
     * @param array $settings field settings
     *
     * @return array
     */
    public function getFrontEndCategories($settings)
    {
        $categories = [];

        if (isset($settings['source'])) {
            $group = explode(':', $settings['source']);
            $pos = count($categories) + 1;

            if (count($group) == 2) {
                $groupById = Craft::$app->getCategories()->getGroupByUid($group[1]);

                $categories[$pos]['categories'] = Category::find()->groupId($group[1])->all();
                $categories[$pos]['group'] = $groupById;
            }
        }

        return $categories;
    }

    /**
     * @param array $settings field settings
     *
     * @return array
     */
    public function getFrontEndTags($settings)
    {
        $tags = [];

        if (isset($settings['source'])) {
            $group = explode(':', $settings['source']);
            $pos = count($tags) + 1;

            if (count($group) == 2) {
                $groupById = Craft::$app->getTags()->getTagGroupByUid($group[1]);

                $tags[$pos]['tags'] = Tag::find()->groupId($group[1])->all();
                $tags[$pos]['group'] = $groupById;
            }
        }

        return $tags;
    }

    private function getSinglesEntries()
    {
        $sections = Craft::$app->getSections()->getSectionsByType(Section::TYPE_SINGLE);
        $singles = [];

        foreach ($sections as $key => $section) {
            $results = Entry::find()->sectionId($section->id)->all();
            $singles[] = $results[0];
        }

        return $singles;
    }
}
