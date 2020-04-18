<?php

namespace barrelstrength\sproutforms\formtemplates;

use barrelstrength\sproutforms\base\FormTemplates;
use Craft;
use craft\web\View;
use yii\base\Exception;

/**
 * The Custom Templates is used to dynamically create a FormTemplates
 * integration when a user selects the custom option and provides a path
 * to the custom templates they wish to use.
 *
 * The Custom Templates integration is not registered with Sprout Forms
 * and will not display in the Form Templates dropdown list.
 */
class CustomTemplates extends FormTemplates
{
    /**
     * @var string
     */
    private $_path;

    /**
     * @return string
     */
    public function getName(): string
    {
        return Craft::t('sprout-forms', 'Custom Templates');
    }

    public function getTemplateMode(): string
    {
        return View::TEMPLATE_MODE_SITE;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getTemplateRoot(): string
    {
        return Craft::$app->path->getSiteTemplatesPath();
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->_path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->_path = $path;
    }
}



