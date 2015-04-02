<?php
/*
 * This file is part of the PommProject/Faker package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Faker;

use PommProject\Faker\Formatter;

/**
 * RowDefinition
 *
 * Defines what kind of data are expected and what formatter to use for Faker.
 *
 * @package Faker
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class RowDefinition
{
    protected $definition = [];
    protected $types;

    /**
     * __construct
     *
     * Can take a table definition as output by the inspector.
     * Default formatters will be used guessed upon fields type. Definition can
     * be overrided after.
     *
     * @access public
     * @param  array $definition
     * @return null
     */
    public function __construct(array $definition = [])
    {
        $this->types = $definition;

        foreach ($definition as $name => $type) {
            $this->setDefinition($name, $this->guessFormatter($type));
        }
    }

    /**
     * setDefinition
     *
     * Set a definition.
     *
     * @access public
     * @param  string           $name
     * @param  Formatter        $formatter
     * @return RowDefinition    $this
     */
    public function setDefinition($name, Formatter $formatter)
    {
        $this->definition[$name] = $formatter;

        return $this;
    }

    /**
     * setFormatterType
     *
     * Shortcut to add a new formetter in the definition.
     *
     * @access public
     * @param  string $name
     * @param  string $type
     * @return $this
     */
    public function setFormatterType($name, $type, array $options = [])
    {
        return $this->setDefinition($name, new Formatter($type, $options));
    }

    /**
     * unsetDefinition
     *
     * Remove a field from definition.
     *
     * @access public
     * @param  string           $name
     * @return RowDefinition    $this
     */
    public function unsetDefinition($name)
    {
        unset($this->definition[$name]);

        return $this;
    }

    /**
     * definitionExists
     *
     * Return true or false whenever a definition exist or not.
     *
     * @access public
     * @param  string $name
     * @return bool
     */
    public function definitionExists($name)
    {
        return (bool) isset($this->definition[$name]);
    }

    /**
     * getDefinition
     *
     * Return the definition
     *
     * @access public
     * @return array
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * getTypes
     *
     * Return the row types;
     *
     * @access public
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * guessFormatter
     *
     * Guess formatter to use from the given postgres type.
     *
     * @access protected
     * @param  string $type
     * @return Formatter
     */
    protected function guessFormatter($type)
    {
        switch ($type) {
        case 'charcter varying':
            // no break
        case 'varchar':
            return new Formatter('sentence');
        case 'timestamp':
            // no break
        case 'timestamptz':
            return new Formatter('iso8601');
        case 'smallint':
            return new Formatter('numberBetween', [-32768, 32767]);
        case 'int4':
            // no break
        case 'int8':
            return new Formatter('randomNumber');
        case 'float4':
            // no break
        case 'float8':
        case 'numeric':
            return new Formatter('randomFloat');
        case 'inet':
            return new Formatter('ipv4');
        default:
            return new Formatter($type);
        }
    }
}
