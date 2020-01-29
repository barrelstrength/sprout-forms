<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\controllers;

use barrelstrength\sproutforms\models\FormGroup as FormGroupModel;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\errors\MissingComponentException;
use craft\web\Controller as BaseController;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class GroupsController extends BaseController
{
    /**
     * Save a group.
     *
     * @return Response
     * @throws \yii\base\Exception
     * @throws BadRequestHttpException
     */
    public function actionSaveGroup(): Response
    {
        $this->requirePostRequest();
        $this->requireAdmin(false);

        $request = Craft::$app->getRequest();

        $group = new FormGroupModel();
        $group->id = $request->getBodyParam('id');
        $group->name = $request->getRequiredBodyParam('name');

        $isNewGroup = (null === $group->id);

        if (SproutForms::$app->groups->saveGroup($group)) {
            if ($isNewGroup) {
                Craft::$app->getSession()->setNotice(Craft::t('sprout-forms', 'Group added.'));
            }

            return $this->asJson([
                'success' => true,
                'group' => $group->getAttributes(),
            ]);
        }

        return $this->asJson([
            'errors' => $group->getErrors(),
        ]);
    }

    /**
     * Deletes a group.
     *
     * @return Response
     * @throws MissingComponentException
     * @throws Exception
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionDeleteGroup(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requireAdmin(false);

        $request = Craft::$app->getRequest();

        $groupId = $request->getRequiredBodyParam('id');
        $success = SproutForms::$app->groups->deleteGroupById($groupId);

        Craft::$app->getSession()->setNotice(Craft::t('sprout-forms', 'Group deleted.'));

        return $this->asJson([
            'success' => $success,
        ]);
    }
}
