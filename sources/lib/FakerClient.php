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

use PommProject\Faker\RowDefinition;
use PommProject\Faker\Exception\FakerException as PommFakerException;
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
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 *
 *
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
            foreach ($this->row_definition->getDefinition() as $name => $formatter) {
                $row[$name] = $this->generator->format(
                    $formatter->formatter, $formatter->options
                );
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
     * @param  array        $rows
     * @return FakerClient  $this
     */
    public function save(array $rows)
    {
        $sql = 'insert into :relation (:fields) values :values';

        $sql = strtr($sql,
            [
                ':relation' => sprintf("%s.%s", $this->schema, $this->table),
                ':fields'   => join(', ', array_keys($this->row_definition->getDefinition())),
                ':values'   => join(', ',
                    array_map($rows, function($val) { return sprintf("(%s)", join(', ', array_values($val))); })
                ),
            ]
        );

        $this
            ->getSession()
            ->getConnection()
            ->executeAnonymousQuery($sql)
            ;

        return $this;
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
