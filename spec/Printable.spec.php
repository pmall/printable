<?php

use Quanta\Printable;

function printable_test () {};

class PrintableTest {
    public function test() {}
    public static function staticTest() {}
    public function __invoke() {}
}


describe('Printable', function () {

    describe('->withStringLimit()', function () {

        it('should return a new Printable with the given string limit', function () {

            $printable = new Printable('test', true);

            $test = $printable->withStringLimit(100);

            $expected = new Printable('test', true, 100);

            expect($test)->toEqual($expected);

        });

    });

    describe('->withArrayLimit()', function () {

        it('should return a new Printable with the given array limit', function () {

            $printable = new Printable('test', true);

            $test = $printable->withArrayLimit(100);

            $expected = new Printable('test', true, 20, 100);

            expect($test)->toEqual($expected);

        });

    });

    describe('->__toString()', function () {

        context('when the callable flag is set to false', function () {

            beforeEach(function () {

                $this->printable = function (...$xs) {
                    return count($xs) == 1
                        ? expect((string) new Printable(array_shift($xs)))
                        : expect((string) new Printable(array_shift($xs), false, ...$xs));
                };

            });

            context('when the value is a boolean', function () {

                context('when the value is true', function () {

                    it('should have (bool) true as string representation', function () {

                        $this->printable(true)->toEqual('(bool) true');

                    });

                });

                context('when the value is false', function () {

                    it('should have (bool) false as string representation', function () {

                        $this->printable(false)->toEqual('(bool) false');

                    });

                });

            });

            context('when the value is an integer', function () {

                it('should have (int) {x} as string representation', function () {

                    $this->printable(1)->toEqual('(int) 1');

                });

            });

            context('when the value is a float', function () {

                it('should have (float) {x} as string representation', function () {

                    $this->printable(1.111)->toEqual('(float) 1.111');

                });

            });

            context('when the value is a string', function () {

                context('when the string is shorter than the limit', function () {

                    it('should have (string) {x} as string representation', function () {

                        $this->printable('01234', 5)->toEqual('(string) \'01234\'');

                    });

                });

                context('when the string is longer than the limit', function () {

                    it('should have (string) {x}... as string representation', function () {

                        $this->printable('0123456789', 5)->toEqual('(string) \'01234...\'');

                    });

                });

            });

            context('when the value is an array', function () {

                context('when the array number of elements is shorter than or equal to the limit', function () {

                    it('should have (array) [{x}] as string representation', function () {

                        $resource = tmpfile();

                        $value = [
                            'k1' => true,
                            1,
                            'k3' => 1.111,
                            'value',
                            'k5' => [],
                            new class {},
                            'k6' => $resource,
                            null,
                        ];

                        $expected = sprintf('(array) [%s]', implode(', ', [
                            "'k1' => (bool) true",
                            "0 => (int) 1",
                            "'k3' => (float) 1.111",
                            "1 => (string) 'value'",
                            "'k5' => (array) [...]",
                            "2 => (object) class@anonymous",
                            sprintf("'k6' => %s", new Printable($resource)),
                            "3 => NULL",
                        ]));

                        $this->printable($value, 20, 8)->toEqual($expected);

                    });

                });

                context('when the array number of elements is greater than the limit', function () {

                    it('should have (array) [{x}, ...] as string representation', function () {

                        $value = range(0, 8);

                        $expected = sprintf('(array) [%s]', implode(', ', [
                            '0 => (int) 0',
                            '1 => (int) 1',
                            '2 => (int) 2',
                            '3 => (int) 3',
                            '4 => (int) 4',
                            '5 => (int) 5',
                            '6 => (int) 6',
                            '7 => (int) 7',
                            '...',
                        ]));

                        $this->printable($value, 20, 8)->toEqual($expected);

                    });

                });

            });

            context('when the value is an object', function () {

                context('when the object is anonymous', function () {

                    it('should have (object) class@anonymous as string representation', function () {

                        $this->printable(new class {})->toEqual('(object) class@anonymous');

                    });

                });

                context('when the object is not anonymous', function () {

                    it('should have (object) {classname} as string representation', function () {

                        $this->printable(new stdClass)->toEqual('(object) stdClass');

                    });

                });

            });

            context('when the value is a resource', function () {

                it('should have (resource) Resource id #{x} as string representation', function () {

                    $this->printable(tmpfile())->toMatch('/^\(resource\) Resource id #[0-9]+$/');

                });

            });

            context('when the value is null', function () {

                it('should have NULL as string representation', function () {

                    $this->printable(null)->toEqual('NULL');

                });

            });

            context('when the value is unknown', function () {

                it('should have (unknown type) as string representation', function () {

                    allow('gettype')->toBeCalled()->andReturn('unknown type');

                    $this->printable('unknown')->toEqual('(unknown type)');

                });

            });

        });

        context('when the callable flag is set to true', function () {

            beforeEach(function () {

                $this->printable = function (...$xs) {
                    return expect((string) new Printable(array_shift($xs), true, ...$xs));
                };

            });

            context('when the value is a boolean', function () {

                it('should still be represented as a boolean', function () {

                    $this->printable(true)->toMatch('/^\(bool\)/');

                });

            });

            context('when the value is an integer', function () {

                it('should still be represented as an integer', function () {

                    $this->printable(1)->toMatch('/^\(int\)/');

                });

            });

            context('when the value is a float', function () {

                it('should still be represented as a float', function () {

                    $this->printable(1.111)->toMatch('/^\(float\)/');

                });

            });

            context('when the value is a string', function () {

                context('when the string is not callable', function () {

                    it('should still be represented as a string', function () {

                        $this->printable('value')->toMatch('/^\(string\)/');

                    });

                });

                context('when the string is callable', function () {

                    context('when the string is a function name', function () {

                        it('should have (callable) {x} as string representation', function () {

                            $this->printable('printable_test')->toEqual('(callable) printable_test');

                        });

                    });

                    context('when the string is a representation of a static method', function () {

                        it('should have (callable) {x} as string representation', function () {

                            $this->printable('PrintableTest::staticTest')->toEqual('(callable) PrintableTest::staticTest');

                        });

                    });

                });

            });

            context('when the value is an array', function () {

                context('when the array is not callable', function () {

                    it('should still be represented as an array', function () {

                        $this->printable([])->toMatch('/^\(array\)/');

                    });

                });

                context('when the array is callable', function () {

                    context('when the array is a representation of a static method', function () {

                        it('should have (callable) [{class}, {method}] as string representation', function () {

                            $expected = sprintf('(callable) [\'%s\', \'staticTest\']', PrintableTest::class);

                            $this->printable([PrintableTest::class, 'staticTest'])->toEqual($expected);

                        });

                    });

                    context('when the array is a representation of an instance method', function () {

                        it('should have (callable) [{object}, {method}] as string representation', function () {

                            $expected = sprintf('(callable) [(object) %s, \'test\']', PrintableTest::class);

                            $this->printable([new PrintableTest, 'test'])->toEqual($expected);

                        });

                    });

                });

            });

            context('when the value is an object', function () {

                context('when the object is not callable', function () {

                    it('should still be represented as an object', function () {

                        $this->printable(new class {})->toMatch('/^\(object\)/');

                    });

                });

                context('when the object is callable', function () {

                    context('when the object is a closure', function () {

                        it('should have (callable) Closure as string representation', function () {

                            $this->printable(function () {})->toEqual('(callable) Closure');

                        });

                    });

                    context('when the object is invokable', function () {

                        it('should have (callable) {class} as string representation', function () {

                            $expected = sprintf('(callable) %s', PrintableTest::class);

                            $this->printable(new PrintableTest)->toEqual($expected);

                        });

                    });

                });

            });

            context('when the value is a resource', function () {

                it('should still be represented as a resource', function () {

                    $this->printable(tmpfile())->toMatch('/^\(resource\)/');

                });

            });

            context('when the value is null', function () {

                it('should have NULL as string representation', function () {

                    $this->printable(null)->toEqual('NULL');

                });

            });

            context('when the value is unknown', function () {

                it('should have (unknown type) as string representation', function () {

                    allow('gettype')->toBeCalled()->andReturn('unknown type');

                    $this->printable('unknown')->toEqual('(unknown type)');

                });

            });

        });

    });

});
