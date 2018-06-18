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
     * The string length limit.
     *
     * @var int
     */
    private $strlim;

    /**
     * The array number of elements limit.
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
    public function __construct($value, int $strlim = 20, int $arrlim = 2)
    {
        $this->value = $value;
        $this->strlim = $strlim;
        $this->arrlim = $arrlim;
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
                return '(bool) ' . $this->boolean($this->value);
                break;
            case 'integer':
                return '(int) ' . $this->value;
                break;
            case 'double':
                return '(double) ' . $this->value;
                break;
            case 'string':
                return '(string) ' . $this->string($this->value);
                break;
            case 'array':
                return '(array) ' . $this->array($this->value);
                break;
            case 'object':
                return '(object) ' . $this->object($this->value);
                break;
            case 'resource':
                return '(resource) ' . (string) $this->value;
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
     * Return a formatted string from the given string.
     *
     * @param string $value
     * @return string
     */
    private function string(string $value): string
    {
        if (! class_exists($value) && strlen($value) > $this->strlim) {
            $value = substr($value, 0, $this->strlim) . '...';
        }

        return '\'' . $value . '\'';
    }

    /**
     * Return a formatted string from the given array.
     *
     * @param array $value
     * @return string
     */
    private function array(array $value): string
    {
        $arr = array_slice($value, 0, $this->arrlim, true);

        $format = function ($k, $v) {
            return $k . ' => ' . (is_array($v)
                ? '(array) [...]'
                : (string) new Printable($v));
        };

        $strs = array_map($format, array_keys($arr), array_values($arr));

        return '[' . implode(', ', $strs) . (count($value) > $this->arrlim ? ', ...]' : ']');
    }

    /**
     * Return a formatted string from the given object.
     *
     * @param object $value
     * @return string
     */
    private function object($value): string
    {
        $reflection = new ReflectionClass($value);

        if ($reflection->isAnonymous()) {

            return 'class@anonymous';

        }

        return $reflection->getName();
    }
}
