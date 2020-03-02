<?php
/**
 * @copyright Copyright (c) 2020 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader-requirejs/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader-requirejs#readme
 */

namespace ischenko\yii2\jsloader\systemjs;

use ischenko\yii2\jsloader\helpers\JsExpression;
use ischenko\yii2\jsloader\JsRendererInterface;
use ischenko\yii2\jsloader\ModuleInterface;
use yii\base\BaseObject;
use yii\helpers\Json;

/**
 * JS renderer for SystemJS
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
class InlineRenderer extends BaseObject implements JsRendererInterface
{
    /**
     * @var array
     */
    private $seen = [];

    /**
     * @var int
     */
    private $moduleIndex = 1;

    /**
     * @param JsExpression $expression
     * @return string
     */
    public function renderJsExpression(JsExpression $expression): string
    {
        $dependencies = $expression->getDependencies();

        while (($code = $expression->getExpression()) instanceof JsExpression) {
            $dependencies = array_merge($dependencies, $code->getDependencies());
            $expression = $code;
        }

        $this->seen = $modules = [];

        foreach ($dependencies as $dependency) {
            $modules = array_merge($this->resolveDependencies($dependency), $modules);
        }

        $modules = array_filter($modules,
            function (ModuleInterface $module) {
                return count($module->getFiles()) == 1;
            });

        $code = $code ?: '';

        /** @var ModuleInterface $module */
        foreach (array_reverse($modules) as $module) {
            $alias = $module->getAlias();

            $import = "System.import(" . Json::encode($alias) . ")";
            $export = $module->getOptions()['systemjs']['exports'] ?? '';

            if (!empty($export)) {
                $code = "var {$export} = __sjs_module_" . $this->moduleIndex . ".default;\n{$code}";
                $code = "function(__sjs_module_" . $this->moduleIndex . ") {\n{$code}\n}";

                $this->moduleIndex++;
            } else {
                $code = "function() {\n{$code}\n}";
            }

            $code = "{$import}.then({$code});";
        }

        return $code;
    }

    /**
     * @param ModuleInterface $module
     * @return ModuleInterface[]
     */
    private function resolveDependencies(ModuleInterface $module): array
    {
        $alias = $module->getAlias();

        if (isset($this->seen[$alias])) {
            return $this->seen[$alias];
        }

        $dependencies = [$alias => $module];

        foreach ($module->getDependencies() as $dependency) {
            $dependencies = array_merge($this->resolveDependencies($dependency), $dependencies);
        }

        return $this->seen[$alias] = $dependencies;
    }
}
