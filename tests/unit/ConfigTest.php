<?php

namespace ischenko\yii2\jsloader\tests\unit\systemjs;

use Codeception\Test\Unit;
use ischenko\yii2\jsloader\base\Module;
use ischenko\yii2\jsloader\ModuleInterface;
use ischenko\yii2\jsloader\systemjs\Config;

class ConfigTest extends Unit
{
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
                ['imports' => ['test' => 'testDep', 'test/' => 'test/url/']]
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
                    $this->construct(Module::class, ['test'], [
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
                        'test/' => 'test/url/',
                        'test' => 'test/js/file1'
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
