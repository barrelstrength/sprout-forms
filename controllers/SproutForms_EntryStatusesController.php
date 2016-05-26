<?php
namespace Craft;

class SproutForms_EntryStatusesController extends BaseController
{
	/**
	 * @param array $variables
	 * @throws HttpException
	 */
	public function actionIndex(array $variables = array())
	{
		$variables['entryStatuses'] = sproutForms()->entries->getAllEntryStatuses();

		$this->renderTemplate('sproutForms/settings/entrystatuses/index', $variables);
	}

	/**
	 * @param array $variables
	 *
	 * @throws HttpException
	 */
	public function actionEdit(array $variables = array())
	{
		$entryStatus   = isset($variables['entryStatus']) ? $variables['entryStatus'] : null;
		$entryStatusId = isset($variables['entryStatusId']) ? $variables['entryStatusId'] : null;

		if (!$entryStatus)
		{
			if ($entryStatusId)
			{
				$entryStatus = sproutForms()->entries->getEntryStatusById($entryStatusId);

				if (!$entryStatus)
				{
					throw new HttpException(404);
				}
			}
			else
			{
				$entryStatus = new SproutForms_EntryStatusModel();
			}
		}

		$this->renderTemplate('sproutforms/settings/entrystatuses/_edit', array(
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

		$id = craft()->request->getPost('entryStatusId');
		$entryStatus = sproutForms()->entries->getEntryStatusById($id);

		if (!$entryStatus)
		{
			$entryStatus = new SproutForms_EntryStatusModel();
		}

		$entryStatus->name    = craft()->request->getPost('name');
		$entryStatus->handle  = craft()->request->getPost('handle');
		$entryStatus->color   = craft()->request->getPost('color');
		$entryStatus->isDefault = craft()->request->getPost('isDefault');

		if (sproutForms()->entries->saveEntryStatus($entryStatus))
		{
			craft()->userSession->setNotice(Craft::t('Entry Status saved.'));

			$this->redirectToPostedUrl();
		}
		else
		{
			craft()->userSession->setError(Craft::t('Could not save Entry Status.'));

			craft()->urlManager->setRouteVariables(array(
				'entryStatus' => $entryStatus
			));
		}
	}

	/**
	 * @return \HttpResponse
	 * @throws HttpException
	 */
	public function actionReorder()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$ids = JsonHelper::decode(craft()->request->getRequiredPost('ids'));

		if ($success = sproutForms()->entries->reorderEntryStatuses($ids))
		{
			return $this->returnJson(array('success' => $success));
		}

		return $this->returnJson(array('error' => Craft::t("Couldn't reorder Order Statuses.")));
	}

	/**
	 * @throws HttpException
	 */
	public function actionDelete()
	{
		$this->requireAjaxRequest();

		$entryStatusId = craft()->request->getRequiredPost('id');

		if (sproutForms()->entries->deleteEntryStatusById($entryStatusId))
		{
			$this->returnJson(array('success' => true));
		}
		else
		{
			$this->returnJson(array('success' => false));
		}
	}

}
