<?php
namespace Craft;

class SproutForms_ContentController extends BaseController
{

	/**
	 * Deletes an entry.
	 * 
	 * @return void
	 */
	public function actionDeleteContent()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$id = craft()->request->getRequiredPost('id');
		$success = craft()->sproutForms->deleteContent($id);
		$this->returnJson(array('success' => $success));
	}
}