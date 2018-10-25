<?php
/**
 * Created by: Andrey Polyakov (andrey@polyakov.im)
 */

namespace Glook\Yii2Model;
use ArrayObject;
use Glook\Yii2Model\base\Model as BaseModel;
use Valitron\Validator;

class Model extends BaseModel
{
    private $_lang = 'en';
    private $_validatorInstance;

    public function __construct($lang = false)
    {
        if ($lang) {
            $this->setLanguage($lang);
        }
        $this->_validatorInstance = new Validator([], [], $this->getLanguage());
        $this->init();
    }
    /**
     * This method is invoked before validation starts.
     * The default implementation raises a `beforeValidate` event.
     * You may override this method to do preliminary checks before validation.
     * Make sure the parent implementation is invoked so that the event can be raised.
     * @return bool whether the validation should be executed. Defaults to true.
     * If false is returned, the validation will stop and the model is considered invalid.
     */
    public function beforeValidate()
    {
        return true;
    }

    /**
     * This method is invoked after validation ends.
     * The default implementation raises an `afterValidate` event.
     * You may override this method to do postprocessing after validation.
     * Make sure the parent implementation is invoked so that the event can be raised.
     */
    public function afterValidate()
    {

    }


    public function setLanguage($value)
    {
        $this->_lang = $value;
    }

    public function getLanguage()
    {
        return $this->_lang;
    }

    public function getValidator()
    {
        return $this->_validatorInstance;
    }

    public function getValidators($refresh = false)
    {

        $validators = new ArrayObject();
        foreach ($this->rules() as $rule) {
            if ($rule instanceof ModelValidator) {
                $validators->append($rule);
            } elseif (is_array($rule) && isset($rule[0], $rule[1])) { // attributes, validator type
                $validator = ModelValidator::createValidator($rule[1], $rule[0], array_slice($rule, 2));
                $validators->append($validator);
            } else {
                throw new \Exception('Invalid validation rule: a rule must specify both attribute names and validator type.');
            }
        }

        return $validators;
    }

    /**
     * Returns a value indicating whether the attribute is required.
     * This is determined by checking if the attribute is associated with a
     * [[\yii\validators\RequiredValidator|required]] validation rule in the
     * current [[scenario]].
     *
     * Note that when the validator has a conditional validation applied using
     * [[\yii\validators\RequiredValidator::$when|$when]] this method will return
     * `false` regardless of the `when` condition because it may be called be
     * before the model is loaded with data.
     *
     * @param string $attribute attribute name
     * @return bool whether the attribute is required
     */
    public function isAttributeRequired($attribute)
    {
        foreach ($this->getActiveValidators($attribute) as $validator) {
            if ($validator->name === 'required') {
                return true;
            }
        }

        return false;
    }


}