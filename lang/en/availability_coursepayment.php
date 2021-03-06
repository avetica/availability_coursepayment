<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language EN
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   : availability_coursepayment
 * @copyright 2016 Mfreak.nl
 * @author    Luuk Verhoeven
 **/
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Restriction by coursepayment';
$string['title'] = 'Coursepayment';
$string['description'] = 'Require a user to pay for a activity first.';
$string['cost'] = 'Cost';
$string['vat'] = 'VAT Percentage included in the cost';
$string['currency'] = 'Currency';
$string['error_invalidnumber'] = 'Add valid number please';
$string['require_condition'] = '<br/>Only available if you pay {$a->cost} {$a->currency}<br/>
{$a->btn}';

$string['currency:eur'] = 'Euro';
$string['currency:usd'] = 'Dollar';
$string['btn:purchase'] = 'Purchase';

$string['privacy:metadata'] = 'The Restriction by coursepayment plugin does not store any personal data.';