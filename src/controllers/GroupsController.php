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
     */
    public function actionSaveGroup()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();

        $group = new FormGroupModel();
        $group->id = $request->getBodyParam('id');
        $group->name = $request->getRequiredBodyParam('name');

        $isNewGroup = empty($group->id);

        if (SproutForms::$app->groups->saveGroup($group)) {
            if ($isNewGroup) {
                Craft::$app->getSession()->setInfo(SproutForms::t('Group added.'));
            }

            return $this->asJson([
                'success' => true,
                'group' => $group->getAttributes(),
            ]);
        } else {
            return $this->asJson([
                'errors' => $group->getErrors(),
            ]);
        }
    }

    /**
     * Deletes a group.
     */
    public function actionDeleteGroup()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();

        $groupId = $request->getRequiredBodyParam('id');
        $success = SproutForms::$app->groups->deleteGroupById($groupId);

        Craft::$app->getSession()->setNotice(SproutForms::t('Group deleted.'));

        return $this->asJson([
            'success' => $success,
        ]);
    }
}