<?php
/**
 * @copyright Copyright (c) 2020 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader-requirejs/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader-requirejs#readme
 */

namespace ischenko\yii2\jsloader\systemjs;

use ischenko\yii2\jsloader\helpers\FileHelper;
use ischenko\yii2\jsloader\ModuleInterface;

/**
 * SystemJs-specific implementation of the configuration
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
class Config extends \ischenko\yii2\jsloader\base\Config
{
    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function toArray(): array
    {
        $this->prepareModules();

        $importMap = ['imports' => [], 'scopes' => []];

        foreach ($this->getModules() as $module) {
            $alias = $module->getAlias();
            $baseUrl = $module->getBaseUrl();

            if (($files = $module->getFiles()) !== []) {
                $importMap['imports'][$alias] = key($files);
            }

            // default folder mapping
            if ($baseUrl !== '') {
                $importMap['imports'][$alias . '/'] = rtrim($baseUrl, '/') . '/';
            }
        }

        return array_filter($importMap);
    }

    /**
     * {@inheritDoc}
     *
     * @param ModuleInterface|string $module
     * @return ModuleInterface
     */
    public function addModule($module): ModuleInterface
    {
        if (!($module instanceof ModuleInterface)) {
            $module = new Module($module);
        }

        return parent::addModule($module);
    }

    /**
     * Prepares modules
     */
    private function prepareModules(): void
    {
        foreach ($this->getModules() as $module) {
            $files = $module->getFiles();

            // skip modules without files or with single file
            if (count($files) <= 1) {
                continue;
            }

            $dependencies = $module->getDependencies();

            $module->clearFiles();
            $module->clearDependencies();

            $prevModule = null;
            $extension = '.js';

            $alias = $module->getAlias();
            $baseUrl = $module->getBaseUrl();
            $regex = '#^' . preg_quote($baseUrl) . '#';

            foreach ($files as $file => $options) {
                $file = FileHelper::removeExtension($file, $extension);
                $newModule = preg_replace($regex, '', $file, 1, $count);

                if ($count > 0 && $baseUrl !== '') {
                    $newModule = FileHelper::normalizePath("{$alias}/{$newModule}", '/');
                }

                $newModule = $this->addModule($newModule)
                    ->addFile($file . $extension, $options)
                    ->setDependencies($dependencies);

                if ($prevModule !== null) {
                    $newModule->addDependency($prevModule);
                }

                $module->addDependency($prevModule = $newModule);
            }
        }
    }
}
