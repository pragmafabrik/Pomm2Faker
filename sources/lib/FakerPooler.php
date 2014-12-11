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

use PommProject\Foundation\Client\ClientPoolerTrait;
use PommProject\Foundation\Client\ClientPoolerInterface;

use Faker\Generator;

/**
 * FakerPooler
 *
 * Faker pooler.
 *
 * @package Faker
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ClientPoolerInterface
 */
class FakerPooler implements ClientPoolerInterface
{
    use ClientPoolerTrait;

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
     * createClient
     *
     * @see ClientPoolerTrait
     */
    public function createClient($identifier)
    {
        $result = str_getcsv($identifier, '.');

        if (count($result) === 1) {
            return new FakerClient($this->generator, $result[0]);
        } else {
            return new FakerClient($this->generator, $result[1], $result[0]);
        }
    }
}
