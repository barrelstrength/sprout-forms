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
    public function getFrontEndEntries($settings): array
    {
        $entries = [];
        $sectionsService = Craft::$app->getSections();

        if (is_array($settings['sources'])) {
            foreach ($settings['sources'] as $source) {
                $section = explode(':', $source);
                $pos = count($entries) + 1;

                if (count($section) == 2) {
                    $sectionModel = $sectionsService->getSectionByUid($section[1]);

                    $entryQuery = Entry::find()->sectionId($sectionModel->id);
                    if ($sectionModel->type == Section::TYPE_CHANNEL){
                        $entryQuery->orderBy(['title' => SORT_ASC]);
                    }

                    $entries[$pos]['entries'] = $entryQuery->all();
                    $entries[$pos]['section'] = $sectionModel;
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
                    $sectionModel = $sectionsService->getSectionById($section->id);

                    $entryQuery = Entry::find()->sectionId($section->id);

                    if ($section->type == Section::TYPE_CHANNEL){
                        $entryQuery->orderBy(['title' => SORT_ASC]);
                    }

                    $entries[$pos]['entries'] = $entryQuery->all();

                    $entries[$pos]['section'] = $sectionModel;
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
    public function getFrontEndCategories($settings): array
    {
        $categories = [];

        if (isset($settings['source'])) {
            $group = explode(':', $settings['source']);
            $pos = count($categories) + 1;

            if (count($group) == 2) {
                $categoryGroup = Craft::$app->getCategories()->getGroupByUid($group[1]);

                $categories[$pos]['categories'] = Category::find()->groupId($categoryGroup->id)->all();
                $categories[$pos]['group'] = $categoryGroup;
            }
        }

        return $categories;
    }

    /**
     * @param array $settings field settings
     *
     * @return array
     */
    public function getFrontEndTags($settings): array
    {
        $tags = [];

        if (isset($settings['source'])) {
            $group = explode(':', $settings['source']);
            $pos = count($tags) + 1;

            if (count($group) == 2) {
                $tagGroup = Craft::$app->getTags()->getTagGroupByUid($group[1]);

                $tags[$pos]['tags'] = Tag::find()->groupId($tagGroup->id)->all();
                $tags[$pos]['group'] = $tagGroup;
            }
        }

        return $tags;
    }

    private function getSinglesEntries(): array
    {
        $sections = Craft::$app->getSections()->getSectionsByType(Section::TYPE_SINGLE);
        $singles = [];

        foreach ($sections as $key => $section) {
            $results = Entry::find()->sectionId($section->id)->orderBy(['title' => SORT_ASC])->all();
            $singles[] = $results[0];
        }

        return $singles;
    }
}
