<?php

namespace barrelstrength\sproutforms\controllers;

use Craft;
use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\models\EntryStatus;
use craft\web\Controller as BaseController;
use yii\web\NotFoundHttpException;

class EntryStatusesController extends BaseController
{
    /**
     * @param array $variables
     *
     * @throws HttpException
     */
    public function actionEdit(int $entryStatusId = null, EntryStatus $entryStatus = null)
    {
        if (!$entryStatus) {
            if ($entryStatusId) {
                $entryStatus = SproutForms::$app->entries->getEntryStatusById($entryStatusId);

                if (!$entryStatus->id) {
                    throw new NotFoundHttpException(SproutForms::t('Entry Status not found'));
                }
            } else {
                $entryStatus = new EntryStatus();
            }
        }

        return $this->renderTemplate('sprout-forms/_settings/entrystatuses/_edit', [
            'entryStatus' => $entryStatus,
            'entryStatusId' => $entryStatusId
        ]);
    }

    /**
     * @throws Exception
     * @throws HttpException
     * @throws \Exception
     */
    public function actionSave()
    {
        $this->requirePostRequest();

        $id = Craft::$app->request->getBodyParam('entryStatusId');
        $entryStatus = SproutForms::$app->entries->getEntryStatusById($id);

        $entryStatus->name = Craft::$app->request->getBodyParam('name');
        $entryStatus->handle = Craft::$app->request->getBodyParam('handle');
        $entryStatus->color = Craft::$app->request->getBodyParam('color');
        $entryStatus->isDefault = Craft::$app->request->getBodyParam('isDefault');

        if (!SproutForms::$app->entries->saveEntryStatus($entryStatus)) {
            Craft::$app->session->setError(SproutForms::t('Could not save Entry Status.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'entryStatus' => $entryStatus
            ]);

            return null;
        }

        Craft::$app->session->setNotice(SproutForms::t('Entry Status saved.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * @return \HttpResponse
     * @throws HttpException
     */
    public function actionReorder()
    {
        $this->requirePostRequest();

        $ids = json_decode(Craft::$app->request->getRequiredBodyParam('ids'), true);

        if ($success = SproutForms::$app->entries->reorderEntryStatuses($ids)) {
            return $this->asJson(['success' => $success]);
        }

        return $this->asJson(['error' => SproutForms::t("Couldn't reorder Order Statuses.")]);
    }

    /**
     * @throws HttpException
     */
    public function actionDelete()
    {
        $this->requirePostRequest();

        $entryStatusId = Craft::$app->request->getRequiredBodyParam('id');

        if (!SproutForms::$app->entries->deleteEntryStatusById($entryStatusId)) {
            $this->asJson(['success' => false]);
        }

        return $this->asJson(['success' => true]);
    }

}
