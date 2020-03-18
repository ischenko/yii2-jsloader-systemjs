<?php

namespace ischenko\yii2\jsloader\tests\unit\systemjs;

use Codeception\Test\Unit;
use ischenko\yii2\jsloader\helpers\JsExpression;
use ischenko\yii2\jsloader\systemjs\InlineRenderer;
use ischenko\yii2\jsloader\systemjs\Module;

class InlineRendererTest extends Unit
{
    /**
     * @dataProvider expressionsProvider
     */
    public function testExpressionRendering($expression, $expected)
    {
        /** @var InlineRenderer $renderer */
        $renderer = $this->construct(InlineRenderer::class);

        verify($renderer->renderJsExpression($expression))->equals($expected);
    }

    public function expressionsProvider()
    {
        return [
            [
                $this->construct(JsExpression::class, []),
                ""
            ],

            [
                $this->construct(JsExpression::class, ['console.log(\'test\');']),
                "console.log('test');"
            ],

            [
                $this->construct(JsExpression::class, [
                    'console.log(\'test\');',
                    [
                        $this->makeEmpty(Module::class,
                            ['getAlias' => 'mod', 'getFiles' => ['testing.js' => []]])
                    ]
                ]),
                "System.import(\"mod\").then(function() {\nconsole.log('test');\n});"
            ],

            [
                $this->construct(JsExpression::class, [
                    'console.log(\'test\');',
                    [
                        $this->makeEmpty(Module::class, [
                            'getAlias' => 'mod',
                            'getExports' => 'export_alias',
                            'getFiles' => ['testing.js' => []]
                        ])
                    ]
                ]),
                "System.import(\"mod\").then(function(__sjs_module_1) {\nvar export_alias = __sjs_module_1.default;\nconsole.log('test');\n});"
            ],

            [
                $this->construct(JsExpression::class, [
                    'console.log(\'test\');',
                    [
                        $this->makeEmpty(Module::class, [
                            'getAlias' => 'mod1',
                            'getExports' => 'export_alias',
                            'getFiles' => ['testing.js' => []]
                        ]),
                        $this->makeEmpty(Module::class, [
                            'getAlias' => 'mod2'
                        ])
                    ]
                ]),
                "System.import(\"mod1\").then(function(__sjs_module_1) {\nvar export_alias = __sjs_module_1.default;\nconsole.log('test');\n});"
            ],

            [
                $this->construct(JsExpression::class, [
                    'console.log(\'test\');',
                    [
                        $this->makeEmpty(Module::class, [
                            'getAlias' => 'mod1',
                            'getExports' => 'export_alias',
                            'getDependencies' => function () use (&$s5mod2) {
                                return [$s5mod2];
                            },
                            'getFiles' => ['testing.js' => []]
                        ]),
                        $s5mod2 = $this->makeEmpty(Module::class, [
                            'getAlias' => 'mod2',
                            'getFiles' => ['testing2.js' => []]
                        ])
                    ]
                ]),
                "System.import(\"mod2\").then(function() {\nSystem.import(\"mod1\").then(function(__sjs_module_1) {\nvar export_alias = __sjs_module_1.default;\nconsole.log('test');\n});\n});"
            ],

            [
                $this->construct(JsExpression::class, [
                    'console.log(\'test\');',
                    [
                        $this->makeEmpty(Module::class, [
                            'getAlias' => 'mod1',
                            'getExports' => 'export_alias',
                            'getFiles' => ['testing.js' => []]
                        ]),
                        $this->makeEmpty(Module::class, [
                            'getAlias' => 'mod2',
                            'getFiles' => ['testing2.js' => []]
                        ])
                    ]
                ]),
                "System.import(\"mod1\").then(function(__sjs_module_1) {\nvar export_alias = __sjs_module_1.default;\nSystem.import(\"mod2\").then(function() {\nconsole.log('test');\n});\n});"
            ],
        ];
    }
}
