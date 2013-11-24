<?php
namespace Craft;

class SenorForm_ContentController extends BaseController
{

	/**
	 * Deletes an entry.
	 */
	public function actionDeleteContent()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$id = craft()->request->getRequiredPost('id');
		$success = craft()->senorForm->deleteContent($id);
		$this->returnJson(array('success' => $success));
	}

}