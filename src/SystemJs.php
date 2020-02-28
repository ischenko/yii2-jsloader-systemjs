<?php
/**
 * @copyright Copyright (c) 2020 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader-requirejs/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader-requirejs#readme
 */

namespace ischenko\yii2\jsloader;

use ischenko\yii2\jsloader\base\Loader;
use yii\web\View;

/**
 * SystemJs implementation
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 * @since 1.0
 */
class SystemJs extends Loader
{
    /**
     * Supported extras:
     *  - amd
     *  - transform
     *  - named-exports
     *  - named-register
     *  - global (ignored if `minimal` set to false)
     *  - module-types (ignored if `minimal` set to false)
     *
     * @var array a list of extras to be loaded
     */
    public $extras = [];

    /**
     * @var bool use minimal loader s.js instead of system.js loader
     */
    public $minimal = true;

    /**
     * @var int|null
     */
    public $position;

    /**
     * {@inheritDoc}
     *
     * @return ConfigInterface
     */
    public function getConfig(): ConfigInterface
    {
        // TODO: Implement getConfig() method.
    }

    /**
     * {@inheritDoc}
     *
     * @param array $jsExpressions
     */
    protected function doRender(array $jsExpressions)
    {
        // TODO: Implement doRender() method.

        $this->registerLibraryFiles();
    }

    /**
     * Register SystemJs files according to the configuration
     */
    private function registerLibraryFiles()
    {
        static $extras;

        if ($extras === null) {
            $extras['system.js'] = ['amd', 'transform', 'named-exports', 'named-register'];
            $extras['s.js'] = array_merge($extras['system.js'], ['global', 'module-types']);
        }

        $view = $this->getView();

        list(, $url) = $view->getAssetManager()->publish('@bower/system.js/dist');

        // resolve script files
        $libFile = $this->minimal ? 's.js' : 'system.js';
        $scripts = array_intersect($this->extras, $extras[$libFile]);
        $scripts = array_map(function ($script) {
            return "extras/{$script}.js";
        }, array_unique($scripts));

        // resolve position
        $position = $this->position ?? View::POS_HEAD;

        if ($position < View::POS_HEAD) {
            $position = View::POS_HEAD;
        } elseif ($position > View::POS_END) {
            $position = View::POS_END;
        }

        $options = ['position' => $position];

        $view->registerJsFile("{$url}/{$libFile}", $options);

        foreach ($scripts as $script) {
            $view->registerJsFile("{$url}/{$script}", $options);
        }
    }
}
