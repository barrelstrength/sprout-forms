<?php

namespace barrelstrength\sproutforms\controllers;

use Craft;
use craft\web\Controller as BaseController;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\models\FormGroup as FormGroupModel;

class GroupsController extends BaseController
{
    /**
     * Save a group.
     *
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveGroup()
    {
        $this->requirePostRequest();

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
     * @return \yii\web\Response
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \craft\errors\MissingComponentException
     */
    public function actionDeleteGroup()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();

        $groupId = $request->getRequiredBodyParam('id');
        $success = SproutForms::$app->groups->deleteGroupById($groupId);

        Craft::$app->getSession()->setNotice(Craft::t('sprout-forms', 'Group deleted.'));

        return $this->asJson([
            'success' => $success,
        ]);
    }
}