<?php
/**
 * Created by: Andrey Polyakov (andrey@polyakov.im)
 */

namespace Glook\Yii2Model\base\helpers;


class BaseInflector
{

    /**
     * @return string
     */
    private static function encoding()
    {
        return 'UTF-8';
    }

    /**
     * Converts a CamelCase name into space-separated words.
     * For example, 'PostTag' will be converted to 'Post Tag'.
     * @param string $name the string to be converted
     * @param bool $ucwords whether to capitalize the first letter in each word
     * @return string the resulting words
     */
    public static function camel2words($name, $ucwords = true)
    {
        $label = mb_strtolower(trim(str_replace([
            '-',
            '_',
            '.',
        ], ' ', preg_replace('/(\p{Lu})/u', ' \0', $name))), self::encoding());
        return $ucwords ? BaseStringHelper::mb_ucwords($label, self::encoding()) : $label;
    }

}