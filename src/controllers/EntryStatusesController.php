<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\controllers;

use barrelstrength\sproutforms\models\EntryStatus;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\Controller as BaseController;
use Exception;
use Throwable;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class EntryStatusesController extends BaseController
{
    /**
     * @param int|null         $entryStatusId
     * @param EntryStatus|null $entryStatus
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionEdit(int $entryStatusId = null, EntryStatus $entryStatus = null): Response
    {
        $this->requireAdmin(false);

        if (!$entryStatus) {
            if ($entryStatusId) {
                $entryStatus = SproutForms::$app->entryStatuses->getEntryStatusById($entryStatusId);

                if (!$entryStatus->id) {
                    throw new NotFoundHttpException('Entry Status not found');
                }

                if ($entryStatus->handle == EntryStatus::SPAM_STATUS_HANDLE) {
                    Craft::$app->session->setError(Craft::t('sprout-forms', "Spam status can't be updated"));

                    return $this->redirect(UrlHelper::cpUrl('sprout-forms/settings/entry-statuses'));
                }
            } else {
                $entryStatus = new EntryStatus();
            }
        }

        return $this->renderTemplate('sprout-forms/settings/entrystatuses/_edit', [
            'entryStatus' => $entryStatus,
            'entryStatusId' => $entryStatusId
        ]);
    }

    /**
     * @return null|Response
     * @throws \yii\base\Exception
     * @throws BadRequestHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        $this->requireAdmin(false);

        $id = Craft::$app->request->getBodyParam('entryStatusId');
        $entryStatus = SproutForms::$app->entryStatuses->getEntryStatusById($id);

        $entryStatus->name = Craft::$app->request->getBodyParam('name');
        $entryStatus->handle = Craft::$app->request->getBodyParam('handle');
        $entryStatus->color = Craft::$app->request->getBodyParam('color');
        $entryStatus->isDefault = Craft::$app->request->getBodyParam('isDefault');

        if (empty($entryStatus->isDefault)) {
            $entryStatus->isDefault = 0;
        }

        if (!SproutForms::$app->entryStatuses->saveEntryStatus($entryStatus)) {
            Craft::$app->session->setError(Craft::t('sprout-forms', 'Could not save Entry Status.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'entryStatus' => $entryStatus
            ]);

            return null;
        }

        Craft::$app->session->setNotice(Craft::t('sprout-forms', 'Entry Status saved.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * @return Response
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionReorder(): Response
    {
        $this->requirePostRequest();
        $this->requireAdmin(false);

        $ids = Json::decode(Craft::$app->request->getRequiredBodyParam('ids'));

        if ($success = SproutForms::$app->entryStatuses->reorderEntryStatuses($ids)) {
            return $this->asJson(['success' => $success]);
        }

        return $this->asJson(['error' => Craft::t('sprout-forms', "Couldn't reorder Order Statuses.")]);
    }

    /**
     * @return Response
     * @throws Exception
     * @throws Throwable
     * @throws StaleObjectException
     * @throws BadRequestHttpException
     */
    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        $this->requireAdmin(false);

        $entryStatusId = Craft::$app->request->getRequiredBodyParam('id');

        if (!SproutForms::$app->entryStatuses->deleteEntryStatusById($entryStatusId)) {
            return $this->asJson(['success' => false]);
        }

        return $this->asJson(['success' => true]);
    }
}
