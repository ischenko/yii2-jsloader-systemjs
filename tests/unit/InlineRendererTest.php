<?php

namespace ischenko\yii2\jsloader\tests\unit\systemjs;

use Codeception\Test\Unit;
use ischenko\yii2\jsloader\helpers\JsExpression;
use ischenko\yii2\jsloader\ModuleInterface;
use ischenko\yii2\jsloader\systemjs\InlineRenderer;

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
                        $this->makeEmpty(ModuleInterface::class,
                            ['getAlias' => 'mod', 'getFiles' => ['testing.js' => []]])
                    ]
                ]),
                "System.import(\"mod\").then(function() {\nconsole.log('test');\n});"
            ],

            [
                $this->construct(JsExpression::class, [
                    'console.log(\'test\');',
                    [
                        $this->makeEmpty(ModuleInterface::class, [
                            'getAlias' => 'mod',
                            'getOptions' => [
                                'systemjs' => ['exports' => 'export_alias']
                            ],
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
                        $this->makeEmpty(ModuleInterface::class, [
                            'getAlias' => 'mod1',
                            'getOptions' => [
                                'systemjs' => ['exports' => 'export_alias']
                            ],
                            'getFiles' => ['testing.js' => []]
                        ]),
                        $this->makeEmpty(ModuleInterface::class, [
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
                        $this->makeEmpty(ModuleInterface::class, [
                            'getAlias' => 'mod1',
                            'getOptions' => [
                                'systemjs' => ['exports' => 'export_alias']
                            ],
                            'getFiles' => ['testing.js' => []]
                        ]),
                        $this->makeEmpty(ModuleInterface::class, [
                            'getAlias' => 'mod2',
                            'getFiles' => ['testing2.js' => []]
                        ])
                    ]
                ]),
                "System.import(\"mod2\").then(function() {\nSystem.import(\"mod1\").then(function(__sjs_module_1) {\nvar export_alias = __sjs_module_1.default;\nconsole.log('test');\n});\n});"
            ],
        ];
    }
}
