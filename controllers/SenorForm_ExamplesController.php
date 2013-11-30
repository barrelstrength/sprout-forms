<?php
namespace Craft;

class SenorForm_ExamplesController extends BaseController
{
	/**
	 * Install examples
	 * @return void
	 */
	public function actionInstall()
	{
    	$this->_installExampleData();
    	$this->_installExampleTemplates();
    	
    	craft()->userSession->setNotice(Craft::t('Examples successfully installed.'));
    	$this->redirect('senorform');
	}
	
	/**
	 * Install templates
	 * @return void
	 */
	private function _installExampleTemplates()
	{
		try 
		{
			$fileHelper = new \CFileHelper();
			@ mkdir(craft()->path->getSiteTemplatesPath() . 'senorform');
			$fileHelper->copyDirectory(craft()->path->getPluginsPath(). 'senorform/examples/templates/senorform', craft()->path->getSiteTemplatesPath() . 'senorform');
		}
		catch (\Exception $e)
		{
			$this->_handle_error($e);
		}
	}
	
	/**
	 * Install data
	 * @return void
	 */
	private function _installExampleData()
	{
		try 
		{
			$sql = file_get_contents(craft()->path->getPluginsPath() . 'senorform/examples/data.sql');
			craft()->db->createCommand($sql)->execute();
		}
		catch (\Exception $e)
		{
			$this->_handleError($e);
		}
	}
	
	/**
	 * Handle installation errors
	 * @param Exception $exception
	 * @return void
	 */
	private function _handleError($exception)
	{
		craft()->userSession->setError(Craft::t('There was an error installing the example, possibly because they are already installed.'));
		$this->redirect('senorform/install_examples');
	}
}