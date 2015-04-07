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

use PommProject\Foundation\Client\ClientPooler;

use Faker\Generator;

/**
 * FakerPooler
 *
 * Faker pooler.
 *
 * @package Faker
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ClientPoolerInterface
 */
class FakerPooler extends ClientPooler
{
    protected $generator;

    /**
     * __construct
     *
     * Constructor
     *
     * @access public
     * @param  Generator $generator
     * @return null
     */
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * getPoolerType
     *
     * @see ClientPoolerInterface
     */
    public function getPoolerType()
    {
        return 'faker';
    }

    /**
     * getClient
     *
     * @see ClientPoolerTrait
     */
    public function getClient($identifier)
    {
        $result = str_getcsv($identifier, '.');

        if (count($result) === 1) {
            $identifier = sprintf("public.%s", $identifier);
        }

        return parent::getClient($identifier);
    }

    /**
     * createClient
     *
     * @see ClientPoolerTrait
     */
    protected function createClient($identifier)
    {
        $result = str_getcsv($identifier, '.');

        return new FakerClient($this->generator, $result[1], $result[0]);
    }
}
