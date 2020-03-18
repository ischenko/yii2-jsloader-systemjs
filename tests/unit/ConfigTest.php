<?php

namespace ischenko\yii2\jsloader\tests\unit\systemjs;

use Codeception\Test\Unit;
use ischenko\yii2\jsloader\base\Module as BaseModule;
use ischenko\yii2\jsloader\ModuleInterface;
use ischenko\yii2\jsloader\systemjs\Config;
use ischenko\yii2\jsloader\systemjs\Module;

class ConfigTest extends Unit
{
    public function testModuleCreation()
    {
        /** @var Config $config */
        $config = $this->construct(Config::class);

        verify($config->addModule('test'))->isInstanceOf(Module::class);
    }

    /**
     * @dataProvider toArrayTestDataProvider
     */
    public function testToArray(array $modules, array $expected)
    {
        /** @var Config $config */
        $config = $this->construct(Config::class);

        foreach ($modules as $module) {
            $config->addModule($module);
        }

        verify($config->toArray())->equals($expected);
    }

    public function toArrayTestDataProvider()
    {
        return [
            [
                [],
                []
            ],

            [
                [$this->makeEmpty(ModuleInterface::class, ['getAlias' => 'test'])],
                []
            ],

            [
                [$this->makeEmpty(ModuleInterface::class, ['getAlias' => 'test', 'getBaseUrl' => 'test/url'])],
                ['imports' => ['test/' => 'test/url/']]
            ],

            [
                [
                    $this->makeEmpty(ModuleInterface::class, [
                        'getAlias' => 'test',
                        'getBaseUrl' => 'test/url',
                        'getDependencies' => [
                            $this->makeEmpty(ModuleInterface::class, [
                                'getAlias' => 'testDep',
                                'getBaseUrl' => 'testDep/url',
                            ])
                        ]
                    ])
                ],
                ['imports' => ['test/' => 'test/url/']]
            ],

            [
                [
                    $this->makeEmpty(ModuleInterface::class, [
                        'getAlias' => 'test',
                        'getBaseUrl' => 'test/url',
                        'getFiles' => [
                            'test/url/js/file1.js' => []
                        ]
                    ])
                ],
                ['imports' => ['test' => 'test/url/js/file1.js', 'test/' => 'test/url/']]
            ],

            [
                [
                    $this->construct(BaseModule::class, ['test'], [
                        'getAlias' => 'test',
                        'getBaseUrl' => 'test/url',
                        'files' => [
                            'test/url/js/file1.js' => [],
                            'test/url/js/file2.js' => [],
                            'test1/url/js/file3.js' => []
                        ]
                    ])
                ],
                [
                    'imports' => [
                        'test/js/file1' => 'test/url/js/file1.js',
                        'test/js/file2' => 'test/url/js/file2.js',
                        'test1/url/js/file3' => 'test1/url/js/file3.js',
                        'test/' => 'test/url/'
                    ]
                ]
            ],

            [
                [
                    $this->makeEmpty(ModuleInterface::class, [
                        'getAlias' => 'test',
                        'getBaseUrl' => 'test/url',
                        'getFiles' => [
                            '//test/url/js/file1.js' => []
                        ]
                    ])
                ],
                ['imports' => ['test' => '//test/url/js/file1.js', 'test/' => 'test/url/']]
            ],
        ];
    }
}
