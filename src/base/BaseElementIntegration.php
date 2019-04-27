<?php

namespace barrelstrength\sproutforms\base;

use Craft;
use craft\elements\User;

/**
 * Class ElementIntegration
 *
 * @package Craft
 */
abstract class BaseElementIntegration extends ApiIntegration
{
    public $authorId;

    public $enableAuthorAsCurrentUser = true;

    /**
     * Default attributes as options
     *
     * @return array
     */
    public function getDefaultAttributes()
    {
        return [
            [
                'label' => Craft::t('app', 'Title'),
                'value' => 'title'
            ]
        ];
    }

    /**
     * @return array
     */
    public function getDefaultElementFieldsAsOptions()
    {
        $options = [];

        if ($this->getDefaultAttributes()){
            foreach ($this->getDefaultAttributes() as $item) {
                $options[] = $item;
            }
        }

        return $options;
    }

    /**
     * @param $elementGroupId
     * @return array
     */
    public function getElementFieldsAsOptions($elementGroupId)
    {
        return [];
    }

    /**
     * @return string
     */
    public function getUserElementType()
    {
        return User::class;
    }

    /**
     * @return User|false|\yii\web\IdentityInterface|null
     */
    public function getAuthor()
    {
        $author = Craft::$app->getUser()->getIdentity();
        if ($this->authorId){
            $user = Craft::$app->getUsers()->getUserById($this->authorId);
            if ($user){
                $author = $user;
            }
        }

        return $author;
    }
}

