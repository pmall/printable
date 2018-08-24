<?php

use Quanta\Printable;

function printable_test () {};

describe('Printable', function () {

    it('should have 20 as default string limit', function () {

        expect(new Printable('test'))->toEqual(new Printable('test', 20));

    });

    it('should have 3 as default array limit', function () {

        expect(new Printable('test'))->toEqual(new Printable('test', 20, 3));

    });

    describe('->withStringLimit()', function () {

        it('should return a new Printable with the given string limit', function () {

            $printable = new Printable('test', 50, 50);

            $test = $printable->withStringLimit(100);

            $expected = new Printable('test', 100, 50);

            expect($test)->toEqual($expected);

        });

    });

    describe('->withArrayLimit()', function () {

        it('should return a new Printable with the given array limit', function () {

            $printable = new Printable('test', 50, 50);

            $test = $printable->withArrayLimit(100);

            $expected = new Printable('test', 50, 100);

            expect($test)->toEqual($expected);

        });

    });

    describe('->__toString()', function () {

        context('when the value is a boolean', function () {

            context('when the value is true', function () {

                it('should have (bool) true as string representation', function () {

                    expect(new Printable(true))->toEqual('true');

                });

            });

            context('when the value is false', function () {

                it('should have (bool) false as string representation', function () {

                    expect(new Printable(false))->toEqual('false');

                });

            });

        });

        context('when the value is an integer', function () {

            it('should have {x} as string representation', function () {

                expect(new Printable(1))->toEqual('1');

            });

        });

        context('when the value is a float', function () {

            it('should have {x} as string representation', function () {

                expect(new Printable(1.111))->toEqual('1.111');

            });

        });

        context('when the value is a string', function () {

            context('when the string is not a class name', function () {

                context('when the string is shorter than the limit', function () {

                    it('should have \'{x}\' as string representation', function () {

                        expect(new Printable('01234', 5))->toEqual('\'01234\'');

                    });

                });

                context('when the string is longer than the limit', function () {

                    it('should have \'{x}...\' as string representation', function () {

                        expect(new Printable('0123456789', 5))->toEqual('\'01234...\'');

                    });

                });

            });

            context('when the string is callable', function () {

                context('when the string is longer than the limit', function () {

                    it('should have \'{x}\' as string representation anyway', function () {

                        expect(new Printable('printable_test', 5))->toEqual('\'printable_test\'');

                    });

                });

            });

            context('when the string is a class name', function () {

                context('when the string is longer than the limit', function () {

                    it('should have \'{x}\' as string representation anyway', function () {

                        expect(new Printable(stdClass::class, 5))->toEqual('\'stdClass\'');

                    });

                });

            });

        });

        context('when the value is an array', function () {

            context('when the array number of elements is shorter than or equal to the limit', function () {

                context('when the array is associative', function () {

                    it('should have [{k} => {v}] as string representation', function () {

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

                        $expected = sprintf('[%s]', implode(', ', [
                            "'k1' => true",
                            "0 => 1",
                            "'k3' => 1.111",
                            "1 => 'value'",
                            "'k5' => [...]",
                            "2 => (instance) class@anonymous",
                            sprintf("'k6' => %s", new Printable($resource)),
                            "3 => NULL",
                        ]));

                        expect(new Printable($value, 20, 8))->toEqual($expected);

                    });

                });

                context('when the array is not associative', function () {

                    it('should have [{v}] as string representation', function () {

                        $resource = tmpfile();

                        $value = [
                            true,
                            1,
                            1.111,
                            'value',
                            [],
                            new class {},
                            $resource,
                            null,
                        ];

                        $expected = sprintf('[%s]', implode(', ', [
                            "true",
                            "1",
                            "1.111",
                            "'value'",
                            "[...]",
                            "(instance) class@anonymous",
                            (string) new Printable($resource),
                            "NULL",
                        ]));

                        expect(new Printable($value, 20, 8))->toEqual($expected);

                    });

                });

            });

            context('when the array number of elements is greater than the limit', function () {

                context('when the sliced array is associative', function () {

                    it('should have [{k} => {v}, ...] as string representation', function () {

                        $value = [
                            'k1' => true,
                            1,
                            'k3' => 1.111,
                            'value',
                            'k5' => [],
                            new class {},
                            'k6' => tmpfile(),
                            null,
                        ];

                        $expected = sprintf('[%s, ...]', implode(', ', [
                            "'k1' => true",
                            "0 => 1",
                            "'k3' => 1.111",
                            "1 => 'value'",
                            "'k5' => [...]",
                            "2 => (instance) class@anonymous",
                        ]));

                        expect(new Printable($value, 20, 6))->toEqual($expected);

                    });

                });

                context('when the sliced array is not associative', function () {

                    it('should have [{v}, ...] as string representation', function () {

                        $value = [
                            true,
                            1,
                            1.111,
                            'value',
                            [],
                            new class {},
                            'k6' => tmpfile(),
                            null,
                        ];

                        $expected = sprintf('[%s, ...]', implode(', ', [
                            "true",
                            "1",
                            "1.111",
                            "'value'",
                            "[...]",
                            "(instance) class@anonymous",
                        ]));

                        expect(new Printable($value, 20, 6))->toEqual($expected);

                    });

                });

            });

        });

        context('when the value is an object', function () {

            context('when the object is anonymous', function () {

                it('should have (instance) class@anonymous as string representation', function () {

                    expect(new Printable(new class {}))->toEqual('(instance) class@anonymous');

                });

            });

            context('when the object is not anonymous', function () {

                context('when the object is not a closure', function () {

                    it('should have (instance) {classname} as string representation', function () {

                        expect(new Printable(new stdClass))->toEqual('(instance) stdClass');

                    });

                });

                context('when the object is a closure', function () {

                    it('should has function {closure}() as string representation', function () {

                        expect(new Printable(function () {}))->toEqual('function {closure}()');

                    });

                });

            });

        });

        context('when the value is a resource', function () {

            it('should have Resource id #{x} as string representation', function () {

                expect((string) new Printable(tmpfile()))->toMatch('/^Resource id #[0-9]+$/');

            });

        });

        context('when the value is null', function () {

            it('should have NULL as string representation', function () {

                expect(new Printable(null))->toEqual('NULL');

            });

        });

        context('when the value is unknown', function () {

            it('should have (unknown type) as string representation', function () {

                allow('gettype')->toBeCalled()->andReturn('unknown type');

                expect(new Printable('unknown'))->toEqual('(unknown type)');

            });

        });

    });

});
