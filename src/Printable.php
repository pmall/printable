<?php declare(strict_types=1);

namespace Quanta;

class Printable
{
    /**
     * The value to represent as a string.
     *
     * @var mixed
     */
    private $value;

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
     * @param int   $strlim
     * @param int   $arrlim
     */
    public function __construct($value, int $strlim = 20, int $arrlim = 3)
    {
        $this->value = $value;
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
        return new Printable($this->value, $strlim, $this->arrlim);
    }

    /**
     * Return a new Printable with the given array limit.
     *
     * @param int $arrlim
     * @return \Quanta\Printable
     */
    public function withArrayLimit(int $arrlim): Printable
    {
        return new Printable($this->value, $this->strlim, $arrlim);
    }

    /**
     * Return a string representation of the value.
     *
     * @return string
     */
    public function __toString()
    {
        $type = gettype($this->value);

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
                return $this->string($this->value);
                break;
            case 'array':
                return $this->array($this->value);
                break;
            case 'object':
                return $this->object($this->value);
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
        return $value ? 'true' : 'false';
    }

    /**
     * Return a formatted string from the given int.
     *
     * @param int $value
     * @return string
     */
    private function int(int $value): string
    {
        return (string) $value;
    }

    /**
     * Return a formatted string from the given float.
     *
     * @param float $value
     * @return string
     */
    private function float(float $value): string
    {
        return (string) $value;
    }

    /**
     * Return a formatted string from the given string.
     *
     * Prepend the string with an ellipsis when it is longer than the limit
     * except when the string is a callable or a class name.
     *
     * @param string $value
     * @return string
     */
    private function string(string $value): string
    {
        $cut = strlen($value) > $this->strlim
            && ! is_callable($value)
            && ! class_exists($value);

        return $cut
            ? $this->quoted(substr($value, 0, $this->strlim) . '...')
            : $this->quoted($value);
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

        $elems = $this->isAssociative($slice)
            ? array_map([$this, 'arrayPair'], array_keys($slice), $slice)
            : array_map([$this, 'arrayValue'], $slice);

        return vsprintf('[%s%s]', [
            implode(', ', $elems),
            count($value) > $this->arrlim ? ', ...' : '',
        ]);
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

        $val_str = $this->arrayValue($val);

        return sprintf('%s => %s', $key_str, $val_str);
    }

    /**
     * Return a formatted strinf for the given array value.
     *
     * @param mixed $val
     * @return string
     */
    private function arrayValue($val): string
    {
        return ! is_array($val) ? (string) new Printable($val) : '[...]';
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

        return $class == \Closure::class
            ? 'function {closure}()'
            : sprintf('(instance) %s', $class);
    }

    /**
     * Return a formatted string from the given resource.
     *
     * @param resource $value
     * @return string
     */
    private function resource($value): string
    {
        return (string) $value;
    }

    /**
     * Return a quoted string.
     *
     * @param string $value
     * @return string
     */
    private function quoted(string $value): string
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

    /**
     * Return whether the given array is associative.
     *
     * @param array $value
     * @return bool
     */
    private function isAssociative(array $value): bool
    {
        return count(array_filter(array_keys($value), 'is_string')) > 0;
    }
}
