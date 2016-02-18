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

require_once($CFG->dirroot . '/theme/bootstrapbase/renderers.php');

/**
 * Clean core renderers.
 *
 * @package    theme_shiny
 * @copyright  2015 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_shiny_core_renderer extends theme_bootstrapbase_core_renderer {

    public function full_header() {
        $html = html_writer::start_tag('header', array('id' => 'page-header', 'class' => 'clearfix'));
        $html .= html_writer::start_div('clearfix', array('id' => 'page-navbar'));
        $html .= html_writer::tag('nav', $this->navbar(), array('class' => 'breadcrumb-nav'));
        $html .= html_writer::div($this->page_heading_button(), 'breadcrumb-button');
        $html .= html_writer::end_div();
        $html .= html_writer::tag('div', $this->course_header(), array('id' => 'course-header'));
        $html .= html_writer::end_tag('header');
        return $html;
    }
    /**
     * Either returns the parent version of the header bar, or a version with the logo replacing the header.
     *
     * @since Moodle 2.9
     * @param array $headerinfo An array of header information, dependant on what type of header is being displayed. The following
     *                          array example is user specific.
     *                          heading => Override the page heading.
     *                          user => User object.
     *                          usercontext => user context.
     * @param int $headinglevel What level the 'h' tag will be.
     * @return string HTML for the header bar.
     */
    public function context_header($headerinfo = null, $headinglevel = 1) {

        if ($this->should_render_logo($headinglevel)) {
            return html_writer::tag('div', '', array('class' => 'logo'));
        }
        return parent::context_header($headerinfo, $headinglevel);
    }

    /**
     * Determines if we should render the logo.
     *
     * @param int $headinglevel What level the 'h' tag will be.
     * @return bool Should the logo be rendered.
     */
    protected function should_render_logo($headinglevel = 1) {
        global $PAGE;

        // Only render the logo if we're on the front page or login page
        // and the theme has a logo.
        if ($headinglevel == 1 && !empty($this->page->theme->settings->logo)) {
            if ($PAGE->pagelayout == 'frontpage' || $PAGE->pagelayout == 'login') {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the navigation bar home reference.
     *
     * The small logo is only rendered on pages where the logo is not displayed.
     *
     * @param bool $returnlink Whether to wrap the icon and the site name in links or not
     * @return string The site name, the small logo or both depending on the theme settings.
     */
    public function navbar_home($returnlink = true) {
        global $CFG;

        if ($this->should_render_logo() || empty($this->page->theme->settings->smalllogo)) {
            // If there is no small logo we always show the site name.
            return $this->get_home_ref($returnlink);
        }
        $imageurl = $this->page->theme->setting_file_url('smalllogo', 'smalllogo');
        $image = html_writer::img($imageurl, get_string('sitelogo', 'theme_' . $this->page->theme->name),
            array('class' => 'small-logo'));

        if ($returnlink) {
            $logocontainer = html_writer::link($CFG->wwwroot, $image,
                array('class' => 'small-logo-container', 'title' => get_string('home')));
        } else {
            $logocontainer = html_writer::tag('span', $image, array('class' => 'small-logo-container'));
        }

        // Sitename setting defaults to true.
        if (!isset($this->page->theme->settings->sitename) || !empty($this->page->theme->settings->sitename)) {
            return $logocontainer . $this->get_home_ref($returnlink);
        }

        return $logocontainer;
    }

    /**
     * Returns a reference to the site home.
     *
     * It can be either a link or a span.
     *
     * @param bool $returnlink
     * @return string
     */
    protected function get_home_ref($returnlink = true) {
        global $CFG, $SITE;

        $sitename = format_string($SITE->shortname, true, array('context' => context_course::instance(SITEID)));

        if ($returnlink) {
            return html_writer::link($CFG->wwwroot, $sitename, array('class' => 'brand', 'title' => get_string('home')));
        }

        return html_writer::tag('span', $sitename, array('class' => 'brand'));
    }
    public function settings_fab() {
        global $SITE;
        if ($this->page->course->id == $SITE->id) {
            $editurl = new moodle_url('/course/view.php', array('id'=>$this->page->course->id, 'sesskey'=>sesskey()));
        } else {
            $editurl = clone($this->page->url);
        }
        $icon = "cog";
        $editurl->param('sesskey', sesskey());
        if ($this->page->user_is_editing()) {
            $editurl->param('edit', 'off');
            $editstring = get_string('turneditingoff');
            $icon = "check";
        } else {
            $editurl->param('edit', 'on');
            $editstring = get_string('turneditingon');
        }
        return '<a href="' . $editurl->out() . '" class="settings" id="navbar-settings"><i class="fa fa-' . $icon . '"></i></a>';
    }
    public function edit_button(moodle_url $url) {
        return '';
    }
}

require_once($CFG->dirroot . "/blocks/settings/renderer.php");

/**
 * Override the settings block renderers
 *
 * @package    theme_shiny
 * @copyright  2016 John Okely <john@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_shiny_block_settings_renderer extends block_settings_renderer {
    public function settings_tree(settings_navigation $navigation) {
        global $PAGE;
        if ($PAGE->pagelayout == 'admin') {
            return $this->tree_siblings_only($navigation);
        }
        $data = new stdClass();
        $data->siteadminlink = new moodle_url('/admin/index.php');
        return $this->render_from_template('block_settings/administration_link', $data);
    }
    private function tree_siblings_only(settings_navigation $navigation) {
        return block_settings_renderer::settings_tree($navigation);;
    }
    public function search_form(moodle_url $formtarget, $searchvalue) {
        global $PAGE;
        if ($PAGE->pagelayout == 'admin') {
            return block_settings_renderer::search_form($formtarget, $searchvalue);
        } else {
            return '';
        }
    }
}
