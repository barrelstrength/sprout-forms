<?php

namespace Craft;

class SproutFormsPlugin extends BasePlugin
{
    function getName()
    {
         $pluginName = Craft::t('Sprout Forms');

        // The plugin name override
        $plugin = craft()->db->createCommand()
                             ->select('settings')
                             ->from('plugins')
                             ->where('class=:class', array(':class'=> 'SproutForms'))
                             ->queryScalar();
        
        $plugin = json_decode( $plugin, true );
        $pluginNameOverride = $plugin['pluginNameOverride'];         

        return ($pluginNameOverride) ? $pluginNameOverride : $pluginName;
    }

    function getVersion()
    {
        return '0.5.1.3';
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
        return craft()->templates->render('sproutforms/settings/_pluginSettings', array(
            'settings' => $this->getSettings()
        ));
    }

    public function registerCpRoutes()
    {
        return array(
            'sproutforms/new' => 
            'sproutforms/_edit',

            'sproutforms/edit\/(?P<formId>\d+)' => 
            'sproutforms/_edit',

            'sproutforms/fields/new' => 
            'sproutforms/fields/_edit',

            'sproutforms/fields/edit/(?P<fieldId>\d+)' => 
            'sproutforms/fields/_edit',
        		
        	'sproutforms/entries/view/(?P<entryId>\d+)' =>
        	'sproutforms/entries/_view',
        		
        	'sproutforms/examples' =>
        	'sproutforms/plugin_settings/install_examples'
        );
    }

    public function onAfterInstall()
    {
		craft()->request->redirect('../sproutforms/settings/examples');
    }
    
    public function dropTables()
    {
		$contentRecord = new SproutForms_ContentRecord();	
		$contentRecord->dropTable();
		
		$fieldRecord = new SproutForms_FieldRecord();
		$fieldRecord->dropTable();
		
		$formRecord = new SproutForms_FormRecord();
		$formRecord->dropTable();
		
		// remove example templates
		$fileHelper = new \CFileHelper();
		$fileHelper->removeDirectory(craft()->path->getSiteTemplatesPath() . 'sproutforms');
    }
}