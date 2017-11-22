<?php
/**
 * VuFind Theme Initializer
 *
 * PHP version 5
 *
 * Copyright (C) The National Library of Finland 2015.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  Theme
 * @author   Samuli Sillanpää <samuli.sillanpaa@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
namespace FinnaTheme;

use Zend\Console\Console;
use Zend\Stdlib\RequestInterface as Request;

/**
 * VuFind Theme Initializer
 *
 * @category VuFind
 * @package  Theme
 * @author   Samuli Sillanpää <samuli.sillanpaa@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
class Initializer extends \VuFindTheme\Initializer
{
    /**
     * Support method for init() -- figure out which theme option is active.
     *
     * @param Request $request Request object (for obtaining user parameters).
     *
     * @return string
     */
    protected function pickTheme(Request $request)
    {
        if (Console::isConsole()) {
            return $this->config->theme;
        } else {
            // Theme is already set up in VuFindTheme initializer
            return $this->tools->getTheme();
        }
    }
}
