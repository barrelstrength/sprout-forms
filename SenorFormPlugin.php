<?php

namespace Craft;

class SenorFormPlugin extends BasePlugin
{
    function getName()
    {
         $pluginName = Craft::t('Señor Form');

        // The plugin name override
        $plugin = craft()->db->createCommand()
                             ->select('settings')
                             ->from('plugins')
                             ->where('class=:class', array(':class'=> 'SenorForm'))
                             ->queryScalar();
        
        $plugin = json_decode( $plugin, true );
        $pluginNameOverride = $plugin['pluginNameOverride'];         

        return ($pluginNameOverride) ? $pluginNameOverride : $pluginName;
    }

    function getVersion()
    {
        return '0.5.1.2';
    }

    function getDeveloper()
    {
        return 'Barrel Strength Design';
    }

    function getDeveloperUrl()
    {
        return 'http://barrelstrengthdesign.com';
    }

    public function hasCpSection()
    {
        return true;
    }


    protected function defineSettings()
    {
        return array(
            'pluginNameOverride'      => AttributeType::String,
        );
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('senorform/settings/_pluginSettings', array(
            'settings' => $this->getSettings()
        ));
    }

    public function registerCpRoutes()
    {
        return array(
            'senorform/new' => 
            'senorform/_edit',

            'senorform/edit\/(?P<formId>\d+)' => 
            'senorform/_edit',

            'senorform/fields/new' => 
            'senorform/fields/_edit',

            'senorform/fields/edit/(?P<fieldId>\d+)' => 
            'senorform/fields/_edit',
        		
        	'senorform/entries/view/(?P<entryId>\d+)' =>
        	'senorform/entries/_view',
        		
        	'senorform/examples' =>
        	'senorform/plugin_settings/install_examples'
        );
    }

    public function onAfterInstall()
    {
		craft()->request->redirect('../senorform/examples');
    }
    
    public function dropTables()
    {
		$contentRecord = new SenorForm_ContentRecord();	
		$contentRecord->dropTable();
		
		$fieldRecord = new SenorForm_FieldRecord();
		$fieldRecord->dropTable();
		
		$formRecord = new SenorForm_FormRecord();
		$formRecord->dropTable();
		
		// remove example templates
		$fileHelper = new \CFileHelper();
		$fileHelper->removeDirectory(craft()->path->getSiteTemplatesPath() . 'senorform');
    }
}