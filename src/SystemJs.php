<?php
/**
 * @copyright Copyright (c) 2020 Roman Ishchenko
 * @license https://github.com/ischenko/yii2-jsloader-requirejs/blob/master/LICENSE
 * @link https://github.com/ischenko/yii2-jsloader-requirejs#readme
 */

namespace ischenko\yii2\jsloader;

use ischenko\yii2\jsloader\base\Loader;
use ischenko\yii2\jsloader\helpers\JsExpression;
use ischenko\yii2\jsloader\systemjs\Config;
use ischenko\yii2\jsloader\systemjs\InlineRenderer;
use yii\di\Instance;
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
     * A list of allowed extras
     */
    const AVAILABLE_EXTRAS = [
        'system' => ['amd', 'transform', 'named-exports', 'named-register'],
        's' => ['amd', 'transform', 'named-exports', 'named-register', 'global', 'module-types']
    ];

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
     * @var string|array|JsRendererInterface
     */
    public $renderer = InlineRenderer::class;

    /**
     * @var Config
     */
    private $config;

    /**
     * {@inheritDoc}
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->renderer = Instance::ensure($this->renderer, JsRendererInterface::class);
    }

    /**
     * {@inheritDoc}
     *
     * @return ConfigInterface
     */
    public function getConfig(): ConfigInterface
    {
        if ($this->config === null) {
            $this->config = new Config();
        }

        return $this->config;
    }

    /**
     * {@inheritDoc}
     *
     * @param JsExpression[] $expressions
     *
     * @return void
     */
    protected function renderJs(array $expressions): void
    {
        $jsCode = '';

        krsort($expressions);

        foreach ($expressions as $pos => $expression) {
            $this->appendJsCode($jsCode, $expression, $pos);
            $jsCode = $expression->render($this->renderer);
        }

        // register systemJs files in the View
        $this->registerLibraryFiles();

        // register JS code at the load position
        $this->getView()->registerJs($jsCode, View::POS_END);

//        if (($importMap = $this->getConfig()->toArray()) !== []) {
//            $options = [
//                'type' => 'systemjs-importmap',
//                'position' => $this->getPosition()
//            ];
//
//            $this->getView()->registerJsFile('', $options);
//        }
    }

    /**
     * Register SystemJs files according to the configuration
     * @throws \yii\base\InvalidConfigException
     */
    protected function registerLibraryFiles()
    {
        $view = $this->getView();

        list(, $url) = $view->getAssetManager()->publish('@bower/system.js/dist');

        // resolve script files
        $libFile = $this->minimal ? 's' : 'system';

        if ($this->minimal) {
            $this->extras[] = 'module-types';
        }

        $scripts = array_intersect($this->extras, self::AVAILABLE_EXTRAS[$libFile]);
        $scripts = array_map(function ($script) {
            return "extras/{$script}";
        }, array_unique($scripts));

        $jsExt = YII_DEBUG ? 'js' : 'min.js';
        $options = ['position' => $this->getPosition()];

        array_unshift($scripts, $libFile);

        foreach ($scripts as $script) {
            $view->registerJsFile("{$url}/{$script}.{$jsExt}", $options);
        }
    }

    /**
     * @param string $js
     * @param JsExpression $expression
     * @param int $pos
     */
    private function appendJsCode(string $js, JsExpression $expression, int $pos)
    {
        while (($code = $expression->getExpression()) instanceof JsExpression) {
            $expression = $code;
        }

        if ($pos === View::POS_READY && !empty($code)) {
            $code = "jQuery(function() {\n{$code}\n})";
        }

        if (!empty($js)) {
            $code .= ";\n{$js}";
        }

        $expression->setExpression($code);
    }

    /**
     * Resolves position for script tags
     *
     * @return int
     */
    private function getPosition(): int
    {
        // resolve position
        $position = $this->position ?? View::POS_HEAD;

        if ($position < View::POS_HEAD) {
            $position = View::POS_HEAD;
        } elseif ($position > View::POS_END) {
            $position = View::POS_END;
        }

        return $position;
    }

}
