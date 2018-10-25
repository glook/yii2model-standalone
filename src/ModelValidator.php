<?php
/**
 * Created by: Andrey Polyakov (andrey@polyakov.im)
 */

namespace  Glook\Yii2Model;


class ModelValidator
{

    public $name;
    public $on = [];
    public $except = [];
    public $options;
    public $safe = true;
    public $attributes;
    public $message = false;
    protected $_useValidator = true;

    public function __construct($name, $attributes, $options = [])
    {
        if (!is_string($name)) throw new \Exception('Validator name must be a string');
        if (in_array($name, ['safe', 'unsafe'])) {
            $this->_useValidator = false;
            if ($name === 'unsafe') $this->safe = false;
        }

        $this->name = $name;
        $this->attributes = $this->stringToArray($attributes);
        $this->options = $this->parse_options($options);
    }


    public static function createValidator($name, $attributes, $options = [])
    {
        return new self($name, $attributes, $options);
    }

    protected function parse_options($options)
    {
        if (isset($options['on'])) {
            $this->on = $this->stringToArray($options['on']);
            unset($options['on']);
        }
        if (isset($options['except'])) {
            $this->except = $this->stringToArray($options['except']);
            unset($options['except']);
        }

        if (isset($options['message'])) {
            $this->message = $options['message'];
            unset($options['message']);
        }

        if (!$this->safe) {
            $this->on = ['***'];
        }
        return $options;
    }

    protected function stringToArray($data)
    {
        if (is_string($data)) $data = [$data];
        return $data;
    }

    /**
     * @param $model TP_Model_Shortcodes_Base
     * @return array|bool
     */
    public function getRule($model, $attribute)
    {
        if ($this->_useValidator) {
            $validator = $model->getValidator();
            $ruleMethod = 'validate' . ucfirst($this->name);

            if (!method_exists($validator, $ruleMethod) && !method_exists($model, $ruleMethod))
                throw new \Exception("Rule '{$this->name}' has not been registered");

            if (method_exists($model, $ruleMethod)) {
                return [[$model, $ruleMethod], $attribute];
            }
            return array_merge([$this->name, $attribute], $this->options);
        }
        return false;
    }

    /**
     * Returns a value indicating whether the validator is active for the given scenario and attribute.
     *
     * A validator is active if
     *
     * - the validator's `on` property is empty, or
     * - the validator's `on` property contains the specified scenario
     *
     * @param string $scenario scenario name
     * @return bool whether the validator applies to the specified scenario.
     */
    public function isActive($scenario)
    {
        return !in_array($scenario, $this->except, true) && (empty($this->on) || in_array($scenario, $this->on, true));
    }

    /**
     * Returns cleaned attribute names without the `!` character at the beginning.
     * @return array attribute names.
     */
    public function getAttributeNames()
    {
        return array_map(function ($attribute) {
            return ltrim($attribute, '!');
        }, $this->attributes);
    }

    /**
     * Validates the specified object.
     * @param \yii\base\Model $model the data model being validated
     * @param array|null $attributes the list of attributes to be validated.
     * Note that if an attribute is not associated with the validator - it will be
     * ignored. If this parameter is null, every attribute listed in [[attributes]] will be validated.
     */
    public function validateAttributes($model, $attributes = null)
    {
        if (is_array($attributes)) {
            $newAttributes = [];
            $attributeNames = $this->getAttributeNames();
            foreach ($attributes as $attribute) {
                if (in_array($attribute, $attributeNames, true)) {
                    $newAttributes[] = $attribute;
                }
            }
            $attributes = $newAttributes;
        } else {
            $attributes = $this->getAttributeNames();
        }

        foreach ($attributes as $attribute) {
            $this->validateAttribute($model, $attribute);
        }
    }

    /**
     * Validates a single attribute.
     * Child classes must implement this method to provide the actual validation logic.
     * @param TP_Model_Shortcodes_Base $model the data model to be validated
     * @param string $attribute the name of the attribute to be validated.
     * @throws /Exception
     */
    public function validateAttribute($model, $attribute)
    {
        $rule = $this->getRule($model, $attribute);
        if ($rule) {
            $baseValidator = $model->getValidator();
            $validator = $baseValidator->withData($model->getAttributes(), [$attribute]);
            $currentRule = $validator->rule(...$rule);
            // adding custom error message if exists
            if ($this->message) {
                $currentRule->message($this->message);
            }
            $validator->labels($model->attributeLabels());
            if (!$validator->validate()) {
                $errors = $validator->errors($attribute);
                if ($errors) {
                    foreach ($errors as $errorMessage) {
                        $model->addError($attribute, $errorMessage);
                    }
                }
            }
        }
    }

}