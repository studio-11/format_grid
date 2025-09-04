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
 * Grid Format.
 *
 * @package   format_grid
 * @copyright &copy; 2023-onwards G J Barnard.
 * @author    G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @copyright 2020 Ferran Recio <ferran@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_grid\output\courseformat\content;

use core\output\renderer_base;
use stdClass;

/**
 * Base class to render a course add section navigation.
 */
class sectionnavigation extends \core_courseformat\output\local\content\sectionnavigation {
    /** @var stdClass the calculated data to prevent calculations when rendered several times */
    protected $data = null;

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $USER;

        if ($this->data !== null) {
            return $this->data;
        }

        $format = $this->format;
        $course = $format->get_course();

        $modinfo = $this->format->get_modinfo();
        $sections = $modinfo->get_section_info_all();

        $data = (object)[
            'previousurl' => '',
            'nexturl' => '',
            'larrow' => $output->larrow(),
            'rarrow' => $output->rarrow(),
            'currentsection' => $this->sectionno,
        ];

        $back = $this->sectionno - 1;
        while ($back >= 0 && empty($data->previousurl)) {
            if ($format->is_section_visible($sections[$back])) { // Different from core.
                if (!$sections[$back]->visible) {
                    $data->previoushidden = true;
                }
                $data->previousname = get_section_name($course, $sections[$back]);
                $data->previousurl = course_get_url($course, $back, ['navigation' => true]);
                $data->hasprevious = true;
            }
            $back--;
        }

        $forward = $this->sectionno + 1;
        $numsections = $format->get_last_section_number_without_deligated();
        while ($forward <= $numsections && empty($data->nexturl)) {
            if ($format->is_section_visible($sections[$forward])) { // Different from core.
                if (!$sections[$forward]->visible) {
                    $data->nexthidden = true;
                }
                $data->nextname = get_section_name($course, $sections[$forward]);
                $data->nexturl = course_get_url($course, $forward, ['navigation' => true]);
                $data->hasnext = true;
            }
            $forward++;
        }

        $data->rtl = right_to_left();
        $this->data = $data;
        return $data;
    }
}
