<?php

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutforms\base\Rule;
use barrelstrength\sproutforms\base\RuleInterface;
use barrelstrength\sproutforms\integrationtypes\MissingIntegration;
use barrelstrength\sproutforms\records\Rules as RulesRecord;
use barrelstrength\sproutforms\SproutForms;
use craft\base\Component;
use craft\db\Query;
use craft\errors\MissingComponentException;
use craft\events\RegisterComponentTypesEvent;
use Craft;
use craft\helpers\Component as ComponentHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\InvalidConfigException;

/**
 *
 * @property array        $integrationOptions
 * @property array        $allConditionalsTypes
 * @property array|Rule[] $allConditionals
 */
class Rules extends Component
{
    const EVENT_REGISTER_CONDITIONALS = 'registerConditionals';

    /**
     * Returns all registered Conditional Logic Types
     *
     * @return array
     */
    public function getAllConditionalsTypes(): array
    {
        $event = new RegisterComponentTypesEvent([
            'types' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_CONDITIONALS, $event);

        return $event->types;
    }

    /**
     * @return Rule[]
     */
    public function getAllConditionals(): array
    {
        $conditionalTypes = $this->getAllConditionalsTypes();

        $conditionals = [];

        foreach ($conditionalTypes as $conditionalType) {
            $conditionals[] = new $conditionalType();
        }

        return $conditionals;
    }

    /**
     * @return array
     */
    public function getRuleOptions(): array
    {
        $conditionals = $this->getAllConditionals();

        $options[] = [
            'label' => Craft::t('sprout-forms', 'Add Rule...'),
            'value' => ''
        ];

        foreach ($conditionals as $conditional) {
            $options[] = [
                'label' => $conditional::displayName(),
                'value' => get_class($conditional)
            ];
        }

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

        $conditionals = [];

        foreach ($results as $result) {
            $conditional = ComponentHelper::createComponent($result, RuleInterface::class);
            $conditionals[] = new $result['type']($conditional);
        }

        return $conditionals;
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
     */
    public function getRulesModal(Rule $rule): array
    {
        $view = Craft::$app->getView();

        $html = $view->renderTemplate('sprout-forms/forms/_editFieldRulesModal', [
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

//    /**
//     * Returns a conditional type selection array grouped by category
//     *
//     * Categories
//     * - Standard conditional
//     * - Custom conditionals that need to be registered using the Sprout Forms Conditional API
//     *
//     * @return array
//     */
//    public function prepareConditionalTypeSelection(): array
//    {
//        $conditionals = $this->getAllConditionals();
//        $standardConditionals = [];
//
//        if (count($conditionals)) {
//            // Loop through registered conditionals and add them to the standard group
//            foreach ($conditionals as $class => $integration) {
//                $standardConditionals[get_class($integration)] = $integration::displayName();
//            }
//
//            // Sort fields alphabetically by name
//            asort($standardConditionals);
//
//            // Add the group label to the beginning of the standard group
//            $standardConditionals = SproutForms::$app->fields->prependKeyValue($standardConditionals, 'standardConditionalsGroup', ['optgroup' => Craft::t('sprout-forms', 'Standard Rules')]);
//        }
//
//        return $standardConditionals;
//    }
}
