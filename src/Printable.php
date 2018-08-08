<?php declare(strict_types=1);

namespace Quanta;

use ReflectionClass;

class Printable
{
    /**
     * The value to represent as a string.
     *
     * @var mixed
     */
    private $value;

    /**
     * Whether the value should be represented as a callable when it's callable.
     *
     * @param bool
     */
    private $callable;

    /**
     * The string length limit.
     *
     * @var int
     */
    private $strlim;

    /**
     * The array size limit.
     *
     * @var int
     */
    private $arrlim;

    /**
     * Constructor.
     *
     * @param mixed $value
     * @param bool  $callable
     * @param int   $strlim
     * @param int   $arrlim
     */
    public function __construct($value, bool $callable = false, int $strlim = 20, int $arrlim = 2)
    {
        $this->value = $value;
        $this->callable = $callable;
        $this->strlim = $strlim;
        $this->arrlim = $arrlim;
    }

    /**
     * Return a new Printable with the given string limit.
     *
     * @param int $strlim
     * @return \Quanta\Printable
     */
    public function withStringLimit(int $strlim): Printable
    {
        return new Printable($this->value, $this->callable, $strlim, $this->arrlim);
    }

    /**
     * Return a new Printable with the given array limit.
     *
     * @param int $arrlim
     * @return \Quanta\Printable
     */
    public function withArrayLimit(int $arrlim): Printable
    {
        return new Printable($this->value, $this->callable, $this->strlim, $arrlim);
    }

    /**
     * Return a string representation of the value.
     *
     * @return string
     */
    public function __toString()
    {
        $type = gettype($this->value);

        $callable = $this->callable && is_callable($this->value);

        switch ($type) {
            case 'boolean':
                return $this->boolean($this->value);
                break;
            case 'integer':
                return $this->int($this->value);
                break;
            case 'double':
                return $this->float($this->value);
                break;
            case 'string':
                return ! $callable
                    ? $this->string($this->value)
                    : $this->callableString($this->value);
                break;
            case 'array':
                return ! $callable
                    ? $this->array($this->value)
                    : $this->callableArray($this->value);
                break;
            case 'object':
                return ! $callable
                    ? $this->object($this->value)
                    : $this->callableObject($this->value);
                break;
            case 'resource':
                return $this->resource($this->value);
                break;
            case 'NULL':
                return 'NULL';
                break;
            default:
                return '(unknown type)';
                break;
        };
    }

    /**
     * Return a formatted string from the given boolean.
     *
     * @param bool $value
     * @return string
     */
    private function boolean(bool $value): string
    {
        return $this->formatted('bool', $value ? 'true' : 'false');
    }

    /**
     * Return a formatted string from the given int.
     *
     * @param int $value
     * @return string
     */
    private function int(int $value): string
    {
        return $this->formatted('int', $value);
    }

    /**
     * Return a formatted string from the given float.
     *
     * @param float $value
     * @return string
     */
    private function float(float $value): string
    {
        return $this->formatted('float', $value);
    }

    /**
     * Return a formatted string from the given string.
     *
     * @param string $value
     * @return string
     */
    private function string(string $value): string
    {
        $str = strlen($value) > $this->strlim
            ? $this->quoted(substr($value, 0, $this->strlim) . '...')
            : $this->quoted($value);

        return $this->formatted('string', $str);
    }

    /**
     * Return a formatted string from the given callable string.
     *
     * @param string $value
     * @return string
     */
    private function callableString(string $value): string
    {
        return $this->formatted('callable', $value);
    }

    /**
     * Return a formatted string from the given array.
     *
     * @param array $value
     * @return string
     */
    private function array(array $value): string
    {
        $slice = array_slice($value, 0, $this->arrlim, true);

        $keys = array_keys($slice);
        $vals = array_values($slice);

        $pairs = array_map([$this, 'arrayPair'], $keys, $vals);

        $str = vsprintf('[%s%s]', [
            implode(', ', $pairs),
            count($value) > $this->arrlim ? ', ...' : '',
        ]);

        return $this->formatted('array', $str);
    }

    /**
     * Return a formatted string for the given key => value pair.
     *
     * @param int|string    $key
     * @param mixed         $val
     * @return string
     */
    private function arrayPair($key, $val): string
    {
        $key_str = is_int($key) ? $key : $this->quoted($key);

        $val_str = is_array($val)
            ? $this->formatted('array', '[...]')
            : new Printable($val);

        return sprintf('%s => %s', $key_str, $val_str);
    }

    /**
     * Return a formatted string from the given callable array.
     *
     * @param array $value
     * @return string
     */
    private function callableArray(array $value): string
    {
        $source = is_string($value[0])
            ? $this->quoted($value[0])
            : $this->object($value[0]);

        $method = $this->quoted($value[1]);

        $str = sprintf('[%s, %s]', $source, $method);

        return $this->formatted('callable', $str);
    }

    /**
     * Return a formatted string from the given object.
     *
     * @param object $value
     * @return string
     */
    private function object($value): string
    {
        $class = $this->classname($value);

        return $this->formatted('object', $class);
    }

    /**
     * Return a formatted string from the given callable object.
     *
     * @param object $value
     * @return string
     */
    private function callableObject($value): string
    {
        $class = $this->classname($value);

        return $this->formatted('callable', $class);
    }

    /**
     * Return a formatted string from the given resource.
     *
     * @param resource $value
     * @return string
     */
    private function resource($value): string
    {
        return $this->formatted('resource', $value);
    }

    /**
     * Return a formatted string.
     *
     * @param string $type
     * @param string $value
     * @return string
     */
    private function formatted(string $type, $value): string
    {
        return sprintf('(%s) %s', $type, $value);
    }

    /**
     * Return a quoted string.
     *
     * @param string $value
     * @return string
     */
    private function quoted($value): string
    {
        return sprintf('\'%s\'', $value);
    }

    /**
     * Return the classname of the given object.
     *
     * @param object $value
     * @return string
     */
    private function classname($value): string
    {
        $class = get_class($value);

        return preg_match('/^class@anonymous/', $class)
            ? 'class@anonymous'
            : $class;
    }
}
