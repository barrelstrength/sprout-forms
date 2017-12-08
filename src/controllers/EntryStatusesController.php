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
	 * @throws HttpException
	 */
	public function actionIndex(array $variables = array())
	{
		$variables['entryStatuses'] = SproutForms::$app->entries->getAllEntryStatuses();

		return $this->renderTemplate('sprout-Forms/settings/entrystatuses/index', $variables);
	}

	/**
	 * @param array $variables
	 *
	 * @throws HttpException
	 */
	public function actionEdit(array $variables = array())
	{
		$entryStatus   = $variables['entryStatus'] ?? $variables['entryStatus'];
		$entryStatusId = $variables['entryStatusId'] ?? $variables['entryStatusId'];

		if (!$entryStatus)
		{
			if ($entryStatusId)
			{
				$entryStatus = SproutForms::$app->entries->getEntryStatusById($entryStatusId);

				if (!$entryStatus)
				{
					throw new NotFoundHttpException(SproutForms::t('Status not found'));
				}
			}
			else
			{
				$entryStatus = new EntryStatus();
			}
		}

		return $this->renderTemplate('sprout-forms/settings/entrystatuses/_edit', array(
			'entryStatus'      => $entryStatus,
			'entryStatusId'    => $entryStatusId
		));
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

		if (!$entryStatus)
		{
			$entryStatus = new EntryStatus();
		}

		$entryStatus->name    = Craft::$app->request->getBodyParam('name');
		$entryStatus->handle  = Craft::$app->request->getBodyParam('handle');
		$entryStatus->color   = Craft::$app->request->getBodyParam('color');
		$entryStatus->isDefault = Craft::$app->request->getBodyParam('isDefault');

		if (!SproutForms::$app->entries->saveEntryStatus($entryStatus))
		{
			Craft::$app->userSession->setError(SproutForms::t('Could not save Entry Status.'));

			Craft::$app->urlManager->setRouteVariables(array(
				'entryStatus' => $entryStatus
			));

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
		$this->getAcceptsJson();

		$ids = JsonHelper::decode(Craft::$app->request->getRequiredBodyParam('ids'));

		if ($success = SproutForms::$app->entries->reorderEntryStatuses($ids))
		{
			return $this->asJson(['success' => $success]);
		}

		return $this->asJson(['error' => SproutForms::t("Couldn't reorder Order Statuses.")]);
	}

	/**
	 * @throws HttpException
	 */
	public function actionDelete()
	{
		$this->getAcceptsJson();

		$entryStatusId = Craft::$app->request->getRequiredBodyParam('id');

		if (!SproutForms::$app->entries->deleteEntryStatusById($entryStatusId))
		{
			$this->asJson(['success' => false]);
		}

		return $this->asJson(['success' => true]);
	}

}
