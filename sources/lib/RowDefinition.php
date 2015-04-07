<?php
/*
 * This file is part of the PragmaFabrik/Pomm/Faker package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PragmaFabrik\Pomm\Faker;

use PragmaFabrik\Pomm\Faker\Exception\FakerException as PommFakerException;

use Faker\Generator;

/**
 * RowDefinition
 *
 * Defines what kind of data are expected and what formatter to use for Faker.
 *
 * @package Faker
 * @copyright 2014 - 2015 Grégoire HUBERT
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
     * @param  array $types
     * @return null
     */
    public function __construct(array $types)
    {
        $this->types = $types;
        $this->flushDefinition();
    }

    /**
     * flushDefinition
     *
     * Flush definition
     *
     * @access public
     * @return RowDefinition    $this
     */
    public function flushDefinition()
    {
        $this->definition = array_fill_keys(array_keys($this->types), null);
    }

    /**
     * setDefinition
     *
     * Set a definition. A definition can be either a scalar or a callable.
     *
     * @access public
     * @param  string               $name
     * @param  mixed                $definition
     * @return RowDefinition        $this
     */
    public function setDefinition($name, $definition)
    {
        $this->checkField($name)->definition[$name] = $definition;

        return $this;
    }

    /**
     * setFormatterType
     *
     * Shortcut to add a new formatter in the definition.
     *
     * @access public
     * @param  string $name
     * @param  string $type
     * @return $this
     */
    public function setFormatterType($name, $type, array $options = [])
    {
        return $this->setDefinition($name, function (Generator $generator) use ($type, $options) {
            return $generator->format($type, $options);
        });
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
        unset($this->checkDefinition($name)->definition[$name]);

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
        return (bool) isset($this->definition[$name]) || array_key_exists($name, $this->definition);
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
     * checkDefinition
     *
     * Throw an exception if the field does not exist.
     *
     * @access private
     * @param  string           $name
     * @throws FakerException
     * @return RowDefinition    $this
     */
    private function checkDefinition($name)
    {
        if ($this->definitionExists($name)) {
            return $this;
        }

        throw new PommFakerException(
            sprintf(
                "No definition for '%s'.",
                $name
            )
        );
    }

    /**
     * checkField
     *
     * Throw an exception id the field does not exist.
     *
     * @access private
     * @param  string           $name
     * @throws FakerException
     * @return RowDefinition    $this
     */
    private function checkField($name)
    {
        if (isset($this->types[$name])) {
            return $this;
        }

        throw new PommFakerException(
            sprintf(
                "No field for '%s'.",
                $name
            )
        );
    }
}
