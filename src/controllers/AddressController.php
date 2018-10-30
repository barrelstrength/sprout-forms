<?php

namespace barrelstrength\sproutforms\controllers;

use barrelstrength\sproutforms\services\Address;
use craft\web\Controller;
use Craft;

class AddressController extends Controller
{

    public $allowAnonymous = true;
    /**
     * @return \yii\web\Response
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionChangeForm()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $formTemplatePath = Craft::getAlias('@barrelstrength/sproutforms/templates/_components/formtemplates/accessible/fields/');
        Craft::$app->view->setTemplatesPath($formTemplatePath);

        $countryCode = Craft::$app->getRequest()->getBodyParam('countryCode');
        $namespace = (Craft::$app->getRequest()->getBodyParam('namespace') != null) ? Craft::$app->getRequest()->getBodyParam('namespace') : 'address';
        $namespace = "fields[$namespace]";
        $addressHelper = new Address();

        $addressHelper->setParams($countryCode, $namespace);

        $html = $addressHelper->getAddressFormHtml();

        return $this->asJson(['html' => $html]);
    }

}