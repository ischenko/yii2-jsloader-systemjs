<?php

namespace ischenko\yii2\jsloader\tests\unit\systemjs;

use Codeception\Specify;
use Codeception\Test\Unit;
use ischenko\yii2\jsloader\systemjs\Module;
use yii\base\InvalidArgumentException;

class ModuleTest extends Unit
{
    use Specify;

    public function testExportsProperty()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->specify('it filters exports value', function ($value, $expected) {
            /** @var Module $module */
            $module = $this->make(Module::class);

            verify($module->getExports())->null();
            verify($module->setExports($value))->same($module);
            verify($module->getExports())->equals($expected);
        }, [
            'examples' => [
                ['', null],
                [' ', null],
                ['test', 'test'],
                [' test', 'test'],
            ]
        ]);

        /** @var Module $module */
        $module = $this->make(Module::class);
        $module->setExports([]);
    }
}
