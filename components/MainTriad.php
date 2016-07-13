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


use trident\Triad;

class MainTriad extends Triad
{
    public function index()
    {
        return [
            'htmlTitle' => 'Simple url minimization',
            'mainContent' => 'Only for demonstration'
        ];
    }

    public function init($options = null)
    {
        parent::init($options);
        $this->defaultConfigForActions['template'] = './data/tpl/layout.tpl.php';
    }
}