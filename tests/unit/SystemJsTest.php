<?php

namespace ischenko\yii2\jsloader\tests\unit\systemjs;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use ischenko\yii2\jsloader\ConfigInterface;
use ischenko\yii2\jsloader\SystemJs;
use yii\web\View;

class SystemJsTest extends Unit
{
    /**
     * @param array $config
     * @param array $expected
     * @dataProvider systemJsConfigsProvider
     */
    public function testSystemJsLoaderRegistering(array $config, array $expected)
    {
        /** @var View $view */
        $view = $this->make(View::class, [
            'registerJsFile' => Expected::exactly(count($expected),
                function ($file, $options) use (&$expected) {
                    verify($options)->hasKey('position');
                    verify($options['position'])->equals(View::POS_HEAD);
                    verify($file)->regExp('#^/assets/[a-z0-9]+/' . preg_quote(array_shift($expected)) . '$#');
                })
        ]);

        /** @var SystemJs $sJs */
        $sJs = $this->construct(SystemJs::class, [$view],
            ['getConfig' => $this->makeEmpty(ConfigInterface::class)]);

        foreach ($config as $key => $value) {
            $sJs->$key = $value;
        }

        $sJs->processAssets();
    }

    public function systemJsConfigsProvider()
    {
        return [
            [[], ['s.js']],
            [['minimal' => true], ['s.js']],
            [['minimal' => false], ['system.js']],
            [['extras' => ['amd', 'test']], ['s.js', 'extras/amd.js']],
            [['extras' => ['amd', 'global']], ['s.js', 'extras/amd.js', 'extras/global.js']],
            [['extras' => ['amd', 'global'], 'minimal' => false], ['system.js', 'extras/amd.js']]
        ];
    }

    /**
     * @param $position
     * @param $expected
     * @dataProvider systemJsScriptsPositionProvider
     */
    public function testScriptsPositionSetting($position, $expected)
    {
        /** @var View $view */
        $view = $this->make(View::class, [
            'registerJsFile' => Expected::atLeastOnce(
                function ($file, $options) use ($expected) {
                    verify($options)->hasKey('position');
                    verify($options['position'])->equals($expected);
                })
        ]);

        /** @var SystemJs $sJs */
        $sJs = $this->construct(SystemJs::class, [$view],
            ['getConfig' => $this->makeEmpty(ConfigInterface::class)]);

        $sJs->position = $position;

        $sJs->processAssets();
    }

    public function systemJsScriptsPositionProvider()
    {
        return [
            [View::POS_HEAD, View::POS_HEAD],
            [View::POS_BEGIN, View::POS_BEGIN],
            [View::POS_END, View::POS_END],
            [View::POS_READY, View::POS_END],
            [View::POS_LOAD, View::POS_END],
        ];
    }
}
