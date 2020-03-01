<?php
/**
 * @copyright Copyright (c) 2020 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader-requirejs/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader-requirejs#readme
 */

namespace ischenko\yii2\jsloader\systemjs;

use ischenko\yii2\jsloader\helpers\JsExpression;
use ischenko\yii2\jsloader\JsRendererInterface;
use yii\base\BaseObject;

/**
 * JS renderer for SystemJS
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
class JsRenderer extends BaseObject implements JsRendererInterface
{
    /**
     * @param JsExpression $expression
     * @return string
     */
    public function renderJsExpression(JsExpression $expression): string
    {
        // TODO: Implement renderJsExpression() method.
    }
}
