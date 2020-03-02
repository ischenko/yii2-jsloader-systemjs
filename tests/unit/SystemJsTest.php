<?php

namespace ischenko\yii2\jsloader\tests\unit\systemjs;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use ischenko\yii2\jsloader\ConfigInterface;
use ischenko\yii2\jsloader\helpers\JsExpression;
use ischenko\yii2\jsloader\JsRendererInterface;
use ischenko\yii2\jsloader\SystemJs;
use ischenko\yii2\jsloader\systemjs\InlineRenderer;
use ischenko\yii2\jsloader\tests\UnitTester;
use yii\base\InvalidConfigException;
use yii\web\AssetBundle;
use yii\web\AssetManager;
use yii\web\View;

/**
 * Class SystemJsTest
 * @property UnitTester $tester
 */
class SystemJsTest extends Unit
{
    public function testRendererInitialization()
    {
        /** @var SystemJs $sJs */
        $sJs = $this->make(SystemJs::class);

        verify($sJs->renderer)->equals(InlineRenderer::class);

        $sJs->init();

        verify($sJs->renderer)->isInstanceOf(InlineRenderer::class);
        verify($sJs->renderer)->isInstanceOf(JsRendererInterface::class);
    }

    public function testRendererInitializationException()
    {
        $this->expectException(InvalidConfigException::class);

        /** @var SystemJs $sJs */
        $sJs = $this->make(SystemJs::class);
        $sJs->renderer = 'invalid';
        $sJs->init();
    }

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
            ['getConfig' => $this->makeEmpty(ConfigInterface::class), 'registerImportMap' => null]);

        foreach ($config as $key => $value) {
            $sJs->$key = $value;
        }

        $sJs->processAssets();
    }

    public function systemJsConfigsProvider()
    {
        return [
            [[], ['s.js', 'extras/module-types.js']],
            [['minimal' => true], ['s.js', 'extras/module-types.js']],
            [['minimal' => false], ['system.js']],
            [['extras' => ['amd', 'test']], ['s.js', 'extras/amd.js', 'extras/module-types.js']],
            [['extras' => ['amd', 'global']], ['s.js', 'extras/amd.js', 'extras/global.js', 'extras/module-types.js']],
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
            ['getConfig' => $this->makeEmpty(ConfigInterface::class), 'registerImportMap' => null]);

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

    /**
     * @dataProvider expressionsRenderDataProvider
     */
    public function testExpressionsRendering($jsBlocks, $expected)
    {
        /** @var View $view */
        $view = $this->make(View::class, [
            'js' => $jsBlocks,
            'registerJs' => Expected::once(function ($js, $position) use ($expected) {
                verify($js)->equals($expected);
                verify($position)->equals(View::POS_END);
            })
        ]);

        /** @var SystemJs $sJs */
        $sJs = $this->construct(SystemJs::class, [$view],
            [
                'getConfig' => $this->makeEmpty(ConfigInterface::class),
                'renderer' => $this->makeEmpty(JsRendererInterface::class, [
                    'renderJsExpression' => Expected::atLeastOnce(function (JsExpression $expression) {
                        return $expression->getExpression();
                    })
                ])
            ]);

        $sJs->setIgnorePositions([]);
        $sJs->processAssets();
    }

    public function expressionsRenderDataProvider()
    {
        return [
            [
                [
                    View::POS_HEAD => [
                        'k1' => 'test1',
                        'k2' => 'test2'
                    ]
                ],
                "test1\ntest2"
            ],
            [
                [
                    View::POS_HEAD => [
                        'k1' => 'test1',
                        'k2' => 'test2'
                    ],
                    View::POS_BEGIN => [
                        'k3' => 'test3',
                        'k4' => 'test4'
                    ]
                ],
                "test1\ntest2;\ntest3\ntest4"
            ],
            [
                [
                    View::POS_HEAD => [
                        'k1' => 'test1',
                        'k2' => 'test2'
                    ],
                    View::POS_BEGIN => [
                        'k3' => 'test3',
                        'k4' => 'test4'
                    ],
                    View::POS_READY => [
                        'k5' => 'test5',
                        'k6' => 'test6'
                    ]
                ],
                "test1\ntest2;\ntest3\ntest4;\njQuery(function() {\ntest5\ntest6\n})"
            ],
            [
                [
                    View::POS_HEAD => [
                        'k1' => 'test1',
                        'k2' => 'test2'
                    ],
                    View::POS_BEGIN => [
                        'k3' => 'test3',
                        'k4' => 'test4'
                    ],
                    View::POS_READY => [
                        'k5' => 'test5',
                        'k6' => 'test6'
                    ],
                    View::POS_END => [
                        'k7' => 'test7',
                        'k8' => 'test8'
                    ]
                ],
                "test1\ntest2;\ntest3\ntest4;\ntest7\ntest8;\njQuery(function() {\ntest5\ntest6\n})"
            ],
            [
                [
                    View::POS_HEAD => [
                        'k1' => 'test1',
                        'k2' => 'test2'
                    ],
                    View::POS_BEGIN => [
                        'k3' => 'test3',
                        'k4' => 'test4'
                    ],
                    View::POS_LOAD => [
                        'k9' => 'test9',
                        'k10' => 'test10'
                    ],
                    View::POS_READY => [
                        'k5' => 'test5',
                        'k6' => 'test6'
                    ],
                    View::POS_END => [
                        'k7' => 'test7',
                        'k8' => 'test8'
                    ]
                ],
                "test1\ntest2;\ntest3\ntest4;\ntest7\ntest8;\njQuery(function() {\ntest5\ntest6\n});\ntest9\ntest10"
            ]
        ];
    }

    /**
     * @dataProvider assetBundlesProvider
     */
    public function testRegisteringImportMap($bundles, $callsCount, $expectedMap)
    {
        /** @var View $view */
        $view = $this->make(View::class, [
            'assetBundles' => $bundles,
            'getAssetManager' => $this->make(AssetManager::class, [
                'publish' => Expected::exactly($callsCount, function ($file) use ($expectedMap) {
                    verify($expectedMap)->equalsJsonFile($file);
                    return [$file, $file];
                })
            ]),
            'registerJsFile' => Expected::exactly($callsCount, function ($js, $options) {
                verify($options)->hasKey('type');
                verify($options['type'])->equals('systemjs-importmap');
                verify($options)->hasKey('position');
                verify($options['position'])->equals(View::POS_HEAD);
            })
        ]);

        /** @var SystemJs $sJs */
        $sJs = $this->construct(SystemJs::class, [$view],
            [
                'renderer' => $this->makeEmpty(JsRendererInterface::class, [
                    'renderJsExpression' => ''
                ]),
                'registerLibraryFiles' => null,
                'registerJsCode' => null
            ]);

        $this->tester->cleanDir(\Yii::getAlias($sJs->runtimePath));

        $sJs->setIgnorePositions([]);
        $sJs->processBundles();
        $sJs->processAssets();
    }

    public function assetBundlesProvider()
    {
        return [
            [
                [
                    'test1' => $this->makeEmpty(AssetBundle::class, ['js' => []])
                ],
                0,
                ''
            ],

            [
                [
                    'test1' => $this->makeEmpty(AssetBundle::class, ['js' => ['testing.js']])
                ],
                1,
                "{
    \"imports\": {
        \"test1\": \"/testing.js\"
    }
}"
            ],

            [
                [
                    'test1' => $this->makeEmpty(AssetBundle::class, ['js' => ['testing.js'], 'baseUrl' => '/url'])
                ],
                1,
                "{
    \"imports\": {
        \"test1\": \"/url/testing.js\",
        \"test1/\": \"/url/\"
    }
}"
            ],

            [
                [
                    'test1' => $this->makeEmpty(AssetBundle::class,
                        ['js' => ['testing.js', 's/testing.js'], 'baseUrl' => '/url'])
                ],
                1,
                "{
    \"imports\": {
        \"test1\": \"test1/testing\",
        \"test1/\": \"/url/\",
        \"test1/testing\": \"/url/testing.js\",
        \"test1/s/testing\": \"/url/s/testing.js\"
    }
}"
            ]
        ];
    }
}
