<?php
namespace Craft;

class SproutForms_ExamplesController extends BaseController
{
	/**
	 * Install examples
	 * 
	 * @return void
	 */
	public function actionInstall()
	{
    	$this->_installExampleData();
    	$this->_installExampleTemplates();
    	
    	craft()->userSession->setNotice(Craft::t('Examples successfully installed.'));
    	$this->redirect('sproutforms');
	}
	
	/**
	 * Install templates
	 * 
	 * @return void
	 */
	private function _installExampleTemplates()
	{
		try 
		{
			$fileHelper = new \CFileHelper();
			@ mkdir(craft()->path->getSiteTemplatesPath() . 'sproutforms');
			$fileHelper->copyDirectory(craft()->path->getPluginsPath(). 'sproutforms/examples/templates/sproutforms', craft()->path->getSiteTemplatesPath() . 'sproutforms');
		}
		catch (\Exception $e)
		{
			$this->_handle_error($e);
		}
	}
	
	/**
	 * Install data
	 * 
	 * @return void
	 */
	private function _installExampleData()
	{
		try 
		{
			$sql = file_get_contents(craft()->path->getPluginsPath() . 'sproutforms/examples/data.sql');
			craft()->db->createCommand($sql)->execute();
		}
		catch (\Exception $e)
		{
			$this->_handleError($e);
		}
	}
	
	/**
	 * Handle installation errors
	 * 
	 * @param Exception $exception
	 * @return void
	 */
	private function _handleError($exception)
	{
		craft()->userSession->setError(Craft::t('There was an error installing the example, possibly because they are already installed.'));
		$this->redirect('sproutforms/install_examples');
	}
}