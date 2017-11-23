<?php

declare(strict_types=1);

namespace App\Tests\Units\Domain;

use atoum;

class WriteOnceHashmap extends atoum
{
    public function test it multiple values are correctly added()
    {
        $this
            ->given(
                $this->newTestedInstance()
            )
            ->when(
                $this->testedInstance->add('foo', 'bar'),
                $this->testedInstance->add('bar', 'baz')
            )
            ->then
                ->integer(count($this->testedInstance))
                    ->isEqualTo(2)
                ->array(iterator_to_array($this->testedInstance))
                    ->isIdenticalTo([
                        'foo' => 'bar',
                        'bar' => 'baz',
                    ])
        ;
    }

    public function test it write only the first added value on multiple same key additions()
    {
        $this
            ->given(
                $this->newTestedInstance()
            )
            ->and(
                $this->testedInstance->add('foo', 'bar')
            )
            ->exception(function () {
                $this->testedInstance->add('foo', 'baz');
            })
            ->isInstanceOf('\LogicException')
                ->message
                    ->contains('Cannot write more than once')
        ;
    }

    public function test empty key is not accepted when adding some value()
    {
        $this
            ->given(
                $this->newTestedInstance()
            )
            ->exception(function () {
                $this->testedInstance->add('', 'bar');
            })
            ->isInstanceOf('\InvalidArgumentException')
        ;
    }
}
