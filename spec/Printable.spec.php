<?php

use Quanta\Printable;

describe('Printable', function () {

    context('when the value is a boolean', function () {

        context('when the value is true', function () {

            it('should have (bool) true as string representation', function () {

                $printable = new Printable(true);

                expect((string) $printable)->toEqual('(bool) true');

            });

        });

        context('when the value is false', function () {

            it('should have (bool) false as string representation', function () {

                $printable = new Printable(false);

                expect((string) $printable)->toEqual('(bool) false');

            });

        });

    });

    context('when the value is an integer', function () {

        it('should have (int) {x} as string representation', function () {

            $printable = new Printable(1);

            expect((string) $printable)->toEqual('(int) 1');

        });

    });

    context('when the value is a double', function () {

        it('should have (double) {x} as string representation', function () {

            $printable = new Printable(1.111);

            expect((string) $printable)->toEqual('(double) 1.111');

        });

    });

    context('when the value is a string', function () {

        context('when the string is shorter than the limit', function () {

            it('should have (string) {x} as string representation', function () {

                $printable = new Printable('01234', 5);

                expect((string) $printable)->toEqual('(string) \'01234\'');

            });

        });

        context('when the string is longer than the limit', function () {

            context('when the string is not a class name', function () {

                it('should have (string) {x}... as string representation', function () {

                    $printable = new Printable('0123456789', 5);

                    expect((string) $printable)->toEqual('(string) \'01234...\'');

                });

            });

            context('when the string is a class name', function () {

                it('should have (string) {x} as string representation regardless its length', function () {

                    $printable = new Printable(stdClass::class, 5);

                    expect((string) $printable)->toEqual('(string) \'stdClass\'');

                });

            });

        });

    });

    context('when the value is an array', function () {

        context('when the array number of elements is shorter than or equal to the limit', function () {

            it('should have (array) [{x}] as string representation', function () {

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

                $printable = new Printable($value, 20, 8);

                expect((string) $printable)->toMatch('/^\(array\) \[' . implode(', ', [
                    'k1 => \(bool\) true',
                    '0 => \(int\) 1',
                    'k3 => \(double\) 1\.111',
                    '1 => \(string\) \'value\'',
                    'k5 => \(array\) \[...\]',
                    '2 => \(object\) class@anonymous',
                    'k6 => \(resource\) Resource id #[0-9]+',
                    '3 => NULL',
                ]) . '\]$/');

            });

        });

        context('when the array number of elements is greater than the limit', function () {

            it('should have (array) [{x}, ...] as string representation', function () {

                $value = range(0, 8);

                $printable = new Printable($value, 20, 8);

                expect((string) $printable)->toEqual('(array) [' . implode(', ', [
                    '0 => (int) 0',
                    '1 => (int) 1',
                    '2 => (int) 2',
                    '3 => (int) 3',
                    '4 => (int) 4',
                    '5 => (int) 5',
                    '6 => (int) 6',
                    '7 => (int) 7',
                    '...',
                ]) . ']');

            });

        });

    });

    context('when the value is an object', function () {

        context('when the object is anonymous', function () {

            it('should have (object) class@anonymous as string representation', function () {

                $printable = new Printable(new class {});

                expect((string) $printable)->toEqual('(object) class@anonymous');

            });

        });

        context('when the object is not anonymous', function () {

            it('should have (object) {classname} as string representation', function () {

                $printable = new Printable(new stdClass);

                expect((string) $printable)->toEqual('(object) stdClass');

            });

        });

    });

    context('when the value is a resource', function () {

        it('should have (resource) Resource id #{x} as string representation', function () {

            $printable = new Printable(tmpfile());

            expect((string) $printable)->toMatch('/^\(resource\) Resource id #[0-9]+$/');

        });

    });

    context('when the value is null', function () {

        it('should have NULL as string representation', function () {

            $printable = new Printable(null);

            expect((string) $printable)->toEqual('NULL');

        });

    });

    context('when the value is unknown', function () {

        it('should have (unknown type) as string representation', function () {

            allow('gettype')->toBeCalled()->andReturn('unknown type');

            $printable = new Printable('unknown');

            expect((string) $printable)->toEqual('(unknown type)');

        });

    });

});
