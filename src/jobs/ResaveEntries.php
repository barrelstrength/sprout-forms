<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\jobs;

use barrelstrength\sproutforms\elements\db\EntryQuery;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\elements\db\ElementQueryInterface;
use craft\queue\BaseJob;
use craft\queue\QueueInterface;
use Exception;
use Throwable;
use yii\queue\Queue;

/**
 * ResaveEntries job
 */
class ResaveEntries extends BaseJob
{
    /**
     * @var int The form entries to be saved
     */
    public $formId;

    /**
     * @var array|null The element criteria that determines which elements should be re-saved
     */
    public $criteria;

    /**
     * @param QueueInterface|Queue $queue
     *
     * @throws Throwable
     */
    public function execute($queue)
    {
        // Let's save ourselves some trouble and just clear all the caches for this element class
        Craft::$app->getTemplateCaches()->deleteCachesByElementType(EntryElement::class);

        /** @var EntryQuery $query */
        $query = $this->_query();
        $total = $query->count();
        $elementsService = Craft::$app->getElements();
        $form = SproutForms::$app->forms->getFormById($this->formId);
        $count = 0;

        foreach ($query->each() as $entry) {
            try {
                $count++;
                $entry->title = Craft::$app->getView()->renderObjectTemplate($form->titleFormat, $entry);
                $entry->resaving = true;
                $elementsService->saveElement($entry);
                $this->setProgress($queue, ($count - 1) / $total, Craft::t('app', '{step} of {total}', [
                    'step' => $count,
                    'total' => $total,
                ]));
            } catch (Exception $e) {
                Craft::error('Title format error: '.$e->getMessage(), __METHOD__);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Resaving Form Entries');
    }

    /**
     * Returns the element query based on the criteria.
     *
     * @return ElementQueryInterface
     */
    private function _query(): ElementQueryInterface
    {
        $query = EntryElement::find();

        if (!empty($this->criteria)) {
            Craft::configure($query, $this->criteria);
        }

        $query->formId = $this->formId;

        $query
            ->offset(null)
            ->limit(null)
            ->orderBy(null);

        return $query;
    }
}
