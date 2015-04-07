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

use PragmaFabrik\Pomm\Faker\RowDefinition;
use PragmaFabrik\Pomm\Faker\Exception\FakerException as PommFakerException;

use PommProject\Foundation\Client\Client;
use PommProject\Foundation\ResultIterator;
use PommProject\Foundation\Session\Session;

use Faker\Generator;

/**
 * FakerClient
 *
 * Pomm2 client for Faker.
 *
 * @package Faker
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see Client
 */
class FakerClient extends Client
{
    protected $table;
    protected $schema;
    protected $row_definition;
    protected $generator;

    public function __construct(Generator $generator, $table, $schema = 'public')
    {
        $this->generator    = $generator;
        $this->table        = $table;
        $this->schema       = $schema;
    }

    /**
     * getClientIdentifier
     *
     * @see ClientInterface
     */
    public function getClientIdentifier()
    {
        return sprintf("%s.%s", $this->schema, $this->table);
    }

    /**
     * getClientType
     *
     * @see ClientInterface
     */
    public function getClientType()
    {
        return 'faker';
    }

    /**
     * initialize
     *
     * @see ClientInterface
     */
    public function initialize(Session $session)
    {
        parent::initialize($session);

        $oid = $this->getSession()
            ->getClientUsingPooler('inspector', null)
            ->getTableOid($this->schema, $this->table);

        if ($oid === null) {
            throw new PommFakerException(
                sprintf(
                    "Could not find information about table '%s.%s'.",
                    $this->schema,
                    $this->table
                )
            );
        }

        $this->row_definition = new RowDefinition(
            $this->extractTypes($this->getSession()
                ->getClientUsingPooler('inspector', null)
                ->getTableFieldInformation($oid)
            )
        );
    }

    /**
     * generate
     *
     * Generate rows.
     *
     * @access public
     * @param  int $count
     * @return array
     */
    public function generate($count = 1)
    {
        $results = [];

        for ($i = 0; $i < $count; $i++) {
            $row = [];

            foreach ($this->row_definition->getDefinition() as $name => $definition) {
                if (is_callable($definition)) {
                    $row[$name] = call_user_func($definition, $this->generator);
                } else {
                    $row[$name] = $definition;
                }
            }

            $results[] = $row;
        }

        return $results;
    }

    /**
     * save
     *
     * Save records in the database.
     *
     * @access public
     * @param  int          $count number of lines to be inserted.
     * @return array        $rows
     */
    public function save($count = 1)
    {
        $sql = strtr(
            'insert into :relation (:fields) values (:types) returning *',
            [
                ':relation' => sprintf("%s.%s", $this->schema, $this->table),
                ':fields'   => join(', ', array_keys($this->row_definition->getDefinition())),
                ':types'   => join(', ',
                array_map(
                    function($val) { return sprintf("\$*::%s", $val); },
                    array_intersect_key($this->row_definition->getTypes(), $this->row_definition->getDefinition())
                )),
            ]
        );

        $manager = $this
            ->getSession()
            ->getClientUsingPooler('query_manager', '\PommProject\Foundation\PreparedQuery\PreparedQueryManager')
            ;
        $rows = [];

        foreach ($this->generate($count) as $row) {
            $rows[] = $manager->query($sql, array_values($row))->get(0);
        }

        return $rows;
    }

    /**
     * getRowDefinition
     *
     * Retur the row definition.
     *
     * @access public
     * @return RowDefinition
     */
    public function getRowDefinition()
    {
        return $this->row_definition;
    }

    /**
     * extractTypes
     *
     * Extract types from inspector.
     *
     * @access private
     * @param  ResultIterator $result
     * @return array
     */
    private function extractTypes(ResultIterator $result)
    {
        $definition = [];

        foreach ($result as $row) {
            $definition[$row['name']] = $row['type'];
        }

        return $definition;
    }
}
