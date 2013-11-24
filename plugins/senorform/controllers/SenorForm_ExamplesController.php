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
    	$this->_install_example_data();
    	$this->_install_example_templates();
    	
    	craft()->userSession->setNotice(Craft::t('Examples successfully installed.'));
    	$this->redirect('senorform/forms');
	}
	
	/**
	 * Install templates
	 * @return void
	 */
	private function _install_example_templates()
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
	private function _install_example_data()
	{
		try 
		{
			$sql = file_get_contents(craft()->path->getPluginsPath() . 'senorform/examples/data.sql');
			craft()->db->createCommand($sql)->execute();
		}
		catch (\Exception $e)
		{
			$this->_handle_error($e);
		}
	}
	
	/**
	 * Handle installation errors
	 * @param Exception $exception
	 * @return void
	 */
	private function _handle_error($exception)
	{
		craft()->userSession->setError(Craft::t('There was an error installing the example, possibly because they are already installed.'));
		$this->redirect('senorform/install_examples');
	}
}