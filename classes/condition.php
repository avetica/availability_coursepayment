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
 * Activity completion condition.
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   : availability_coursepayment
 * @copyright 2016 Mfreak.nl
 * @author    Luuk Verhoeven
 **/

namespace availability_coursepayment;

use coding_exception;
use core_availability\info;
use dml_exception;
use html_writer;
use moodle_exception;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class condition
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   : availability_coursepayment
 * @copyright 2016 Mfreak.nl
 * @author    Luuk Verhoeven
 */
class condition extends \core_availability\condition {

    /**
     * @var float $cost
     */
    protected $cost = 10;

    /**
     * @var string
     */
    protected $currency = 'EUR';

    /**
     * @var int
     */
    protected $vat = 21;

    /**
     * condition constructor.
     *
     * @param $structure
     */
    public function __construct($structure) {

        if (property_exists($structure, 'cost')) {
            $this->cost = abs($structure->cost);
        } else {
            $this->cost = 10;
        }

        if (property_exists($structure, 'currency')) {
            $this->currency = $structure->currency;
        } else {
            $this->currency = 'EUR';
        }

        if (property_exists($structure, 'vat')) {
            $this->vat = $structure->vat;
        } else {
            $this->vat = 21;
        }

        // Throw errors??
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param int    $cost
     * @param int    $vat
     * @param string $currency
     *
     * @return object
     */
    public static function get_json($cost = 10, $vat = 21, $currency = 'EUR') {
        return (object)[
            'type' => 'coursepayment',
            'cost' => (float)$cost,
            'vat' => (int)$vat,
            'currency' => $currency,
        ];
    }

    /**
     * Saves tree data back to a structure object.
     *
     * @return stdClass Structure object (ready to be made into JSON format)
     */
    public function save() {
        // Save back the data into a plain array similar to $structure above.
        return (object)[
            'type' => 'coursepayment',
            'cost' => $this->cost,
            'currency' => $this->currency,
            'vat' => $this->vat,
        ];
    }

    /**
     * Determines whether a particular item is currently available
     * according to this availability condition.
     *
     * If implementations require a course or modinfo, they should use
     * the get methods in $info.
     *
     * The $not option is potentially confusing. This option always indicates
     * the 'real' value of NOT. For example, a condition inside a 'NOT AND'
     * group will get this called with $not = true, but if you put another
     * 'NOT OR' group inside the first group, then a condition inside that will
     * be called with $not = false. We need to use the real values, rather than
     * the more natural use of the current value at this point inside the tree,
     * so that the information displayed to users makes sense.
     *
     * @param bool                    $not        Set true if we are inverting the condition
     * @param info $info       Item we're checking
     * @param bool                    $grabthelot Performance hint: if true, caches information
     *                                            required for all course-modules, to make the front page and similar
     *                                            pages work more quickly (works only for current user)
     * @param int                     $userid     User ID to check availability for
     *
     * @return bool True if available
     * @throws dml_exception
     */
    public function is_available($not, info $info, $grabthelot, $userid) {
        if ($info->get_context()->contextlevel == CONTEXT_MODULE) {
            // Cmd.
            return helper::user_can_access_cmid($info->get_context()->instanceid, $userid);
        }

        // Section.
        return helper::user_can_access_section($info->get_section()->section, $info->get_course()->id, $userid);
    }

    /**
     * Obtains a string describing this restriction (whether or not
     * it actually applies). Used to obtain information that is displayed to
     * students if the activity is not available to them, and for staff to see
     * what conditions are.
     *
     * The $full parameter can be used to distinguish between 'staff' cases
     * (when displaying all information about the activity) and 'student' cases
     * (when displaying only conditions they don't meet).
     *
     * If implementations require a course or modinfo, they should use
     * the get methods in $info.
     *
     * The special string <AVAILABILITY_CMNAME_123/> can be returned, where
     * 123 is any number. It will be replaced with the correctly-formatted
     * name for that activity.
     *
     * @param bool                    $full Set true if this is the 'full information' view
     * @param bool                    $not  Set true if we are inverting the condition
     * @param info $info Item we're checking
     *
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function get_description($full, $not, info $info) {

        $params = [
            'courseid' =>
                $info->get_course()->id,
        ];
        if ($info->get_context()->contextlevel == CONTEXT_MODULE) {
            // Module params.
            $params += [
                'cmid' => $info->get_context()->instanceid,
                'contextlevel' => CONTEXT_MODULE,
            ];
        } else {
            // Section params.
            $params += [
                'contextlevel' => CONTEXT_COURSE,
                'section' => $info->get_section()->section,
            ];
        }

        // This function just returns the information that shows about
        // the condition on editing screens. Usually it is similar to
        // the information shown if the user doesn't meet the
        // condition (it does not depend on the current user).
        // $course = $info->get_course();
        $obj = new stdClass();
        $obj->cost = helper::price($this->cost);
        $obj->currency = get_string('currency:' . strtolower($this->currency), 'availability_coursepayment');
        $obj->vat = $this->vat;
        $obj->btn = html_writer::link(new moodle_url('/availability/condition/coursepayment/payment.php', $params), get_string('btn:purchase', 'availability_coursepayment'), [
            'class' => 'btn btn-primary',
        ]);

        return get_string('require_condition', 'availability_coursepayment', $obj);
    }

    /**
     * Obtains a representation of the options of this condition as a string,
     * for debugging.
     *
     * @return string Text representation of parameters
     */
    protected function get_debug_string() {
        // This function is only normally used for unit testing and
        // stuff like that. Just make a short string representation
        // of the values of the condition, suitable for developers.
        return ($this->cost > 0) ? 'cost ON' : 'cost OFF';
    }
}