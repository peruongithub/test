<?php
/**
 * This file is part of the Trident package.
 *
 * Perederko Ruslan <perederko.ruslan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace components;


class UserError extends \Exception
{
    protected $message = '';

    /**
     * UserError constructor.
     * @param string $message
     * @param array $arguments
     */
    public function __construct($message, array $arguments = [])
    {
        $this->message = sprintf((string)$message, $arguments);
    }
}