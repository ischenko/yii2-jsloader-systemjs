<?php
/**
 * @copyright Copyright (c) 2020 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader-requirejs/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader-requirejs#readme
 */

namespace ischenko\yii2\jsloader\systemjs;

use ischenko\yii2\jsloader\ModuleInterface;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;

/**
 * Implementation of a module for RequireJS
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
class Module extends \ischenko\yii2\jsloader\base\Module
{
    /**
     * @var string|null
     */
    private $exports;

    /**
     * @return string|null
     */
    public function getExports(): ?string
    {
        return $this->exports;
    }

    /**
     * Sets value for the exports section of shim config
     *
     * @param string|null $exports
     * @return $this
     *
     * @throws InvalidArgumentException
     *
     * @see http://requirejs.org/docs/api.html#config-shim
     */
    public function setExports($exports): ModuleInterface
    {
        if ($exports === null) {
            $this->exports = null;
        } else {
            if (!is_string($exports)) {
                throw new InvalidArgumentException('Exports must be a string');
            }

            $this->exports = trim($exports);
            $this->exports = $this->exports ?: null;
        }

        return $this;
    }

    /**
     * @param array $options options for a module. Loads settings from requirejs key
     * @return $this
     */
    public function setOptions(array $options): ModuleInterface
    {
        $sjsOptions = ArrayHelper::remove($options, 'systemjs', []);

        foreach ($sjsOptions as $key => $value) {
            $this->$key = $value;
        }

        return parent::setOptions($options);
    }
}
