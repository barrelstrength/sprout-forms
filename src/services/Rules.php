<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutforms\base\Rule;
use barrelstrength\sproutforms\base\RuleInterface;
use barrelstrength\sproutforms\integrationtypes\MissingIntegration;
use barrelstrength\sproutforms\records\Rules as RulesRecord;
use barrelstrength\sproutforms\rules\FieldRule;
use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\errors\MissingComponentException;
use craft\helpers\Component as ComponentHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * @property array $integrationOptions
 * @property array $ruleOptions
 */
class Rules extends Component
{
    /**
     * @return array
     */
    public function getRuleOptions(): array
    {
        $options[] = [
            'label' => Craft::t('sprout-forms', 'Add Rule...'),
            'value' => ''
        ];

        $options[] = [
            'label' => FieldRule::displayName(),
            'value' => FieldRule::class
        ];

        return $options;
    }

    /**
     * @param $formId
     * @param $type
     * @param $enabled
     *
     * @return array
     * @throws InvalidConfigException
     * @throws MissingComponentException
     */
    public function getRulesByFormId($formId, $type = null, $enabled = null): array
    {
        $query = (new Query())
            ->select([
                'rules.id',
                'rules.formId',
                'rules.name',
                'rules.type',
                'rules.behaviorAction',
                'rules.behaviorTarget',
                'rules.settings',
                'rules.enabled'
            ])
            ->from(['{{%sproutforms_rules}} rules'])
            ->where(['rules.formId' => $formId]);

        if ($type !== null) {
            $query->andWhere('rules.type = :type', [':type' => $type]);
        }

        if ($enabled !== null) {
            $query->andWhere('rules.enabled = :enabled', [':enabled' => $enabled]);
        }

        $results = $query->all();

        $rules = [];

        foreach ($results as $result) {
            $rule = ComponentHelper::createComponent($result, RuleInterface::class);
            $rules[] = new $result['type']($rule);
        }

        return $rules;
    }

    /**
     * @param $ruleId
     *
     * @return Rule|null
     * @throws InvalidConfigException
     * @throws MissingComponentException
     */
    public function getRuleById($ruleId)
    {
        $result = (new Query())
            ->select([
                'rules.id',
                'rules.formId',
                'rules.name',
                'rules.type',
                'rules.behaviorAction',
                'rules.behaviorTarget',
                'rules.settings',
                'rules.enabled'
            ])
            ->from(['{{%sproutforms_rules}} rules'])
            ->where(['rules.id' => $ruleId])
            ->one();

        if (!$result) {
            return null;
        }

        /** @var Rule $rule * */
        $rule = ComponentHelper::createComponent($result, RuleInterface::class);

        return new $result['type']($rule);
    }

    /**
     * @param Rule $rule
     *
     * @return bool
     */
    public function saveRule(Rule $rule): bool
    {
        if ($rule->id) {
            $conditionalRecord = RulesRecord::findOne($rule->id);
        } else {
            $conditionalRecord = new RulesRecord();
        }

        $conditionalRecord->type = get_class($rule);
        $conditionalRecord->formId = $rule->formId;
        $conditionalRecord->name = $rule->name ?? $rule::displayName();
        $conditionalRecord->enabled = $rule->enabled;
        $conditionalRecord->behaviorAction = $rule->behaviorAction;
        $conditionalRecord->behaviorTarget = $rule->behaviorTarget;

        $conditionalRecord->settings = $rule->getSettings();

        if ($conditionalRecord->save()) {
            $rule->id = $conditionalRecord->id;
            $rule->name = $conditionalRecord->name;

            return true;
        }

        return false;
    }

    /**
     * @param $config
     *
     * @return RuleInterface
     * @throws InvalidConfigException
     */
    public function createRule($config): RuleInterface
    {
        if (is_string($config)) {
            $config = ['type' => $config];
        }

        try {
            /** @var Rule $rule */
            $rule = ComponentHelper::createComponent($config, RuleInterface::class);
        } catch (MissingComponentException $e) {
            $config['errorMessage'] = $e->getMessage();
            $config['expectedType'] = $config['type'];
            unset($config['type']);

            $rule = new MissingIntegration($config);
        }

        return $rule;
    }

    /**
     * Loads the sprout modal conditional via ajax.
     *
     * @param Rule $rule
     *
     * @return array
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getRulesModal(Rule $rule): array
    {
        $view = Craft::$app->getView();

        $html = $view->renderTemplate('sprout-forms/forms/_editRuleModal', [
            'rule' => $rule,
        ]);

        $js = $view->getBodyHtml();
        $css = $view->getHeadHtml();

        return [
            'html' => $html,
            'js' => $js,
            'css' => $css
        ];
    }
}
