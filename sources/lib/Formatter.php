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

/**
 * Formatter
 *
 * Defines a Faker formatter with its options.
 *
 * @package Faker
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class Formatter
{
    public $formatter;
    public $options = [];

    /**
     * __construct
     *
     * Set formatter.
     *
     * @access public
     * @param  string $formatter
     * @param  array $options
     * @return null
     */
    public function __construct($formatter, array $options = [])
    {
        $this->formatter = $formatter;
        $this->options   = $options;
    }
}
