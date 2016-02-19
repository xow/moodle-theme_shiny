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
 * @copyright  2016 John Okely <john@moodle.com>
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
    public function hamburger() {
        return '<a href="#" class="hamburger nav-icon nav-icon-large" id="navbar-hamburger"><i class="fa fa-bars"></i></a>';
    }
    public function user_menu($user = null, $withlinks = null) {
        $output = '';
        $messageurl = new moodle_url('/message/index.php');
        $output .= '<a href="#" class="nav-icon nav-icon"><i class="fa fa-globe"></i></a>';
        $output .= '<a href="' . $messageurl->out() . '" class="nav-icon nav-icon"><i class="fa fa-envelope"></i></a>';
        $output .= core_renderer::user_menu($user, $withlinks);
        return $output;
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
    protected function navigation_node(navigation_node $node, $attrs=array(), $depth = 1) {
        $items = $node->children;

        // exit if empty, we don't want an empty ul element
        if ($items->count()==0) {
            return '';
        }

        // array of nested li elements
        $lis = array();
        $number = 0;
        foreach ($items as $item) {
            $number++;
            if (!$item->display) {
                continue;
            }

            $isbranch = ($item->children->count()>0  || $item->nodetype==navigation_node::NODETYPE_BRANCH);
            $hasicon = (!$isbranch && $item->icon instanceof renderable);

            if ($isbranch) {
                $item->hideicon = true;
            }
            $content = $this->output->render($item);

            // this applies to the li item which contains all child lists too
            $liclasses = array($item->get_css_type());
            $liexpandable = array();
            if ($isbranch) {
                $liclasses[] = 'contains_branch';
                if (!$item->forceopen || (!$item->forceopen && $item->collapse) || ($item->children->count() == 0
                        && $item->nodetype == navigation_node::NODETYPE_BRANCH)) {
                    $liexpandable = array('aria-expanded' => 'false');
                } else {
                    $liexpandable = array('aria-expanded' => 'true');
                }
                if ($item->requiresajaxloading) {
                    $liexpandable['data-requires-ajax'] = 'true';
                    $liexpandable['data-loaded'] = 'false';
                }

            } else if ($hasicon) {
                $liclasses[] = 'item_with_icon';
            }
            if ($item->isactive === true) {
                $liclasses[] = 'current_branch';
            }
            $nodetextid = 'label_' . $depth . '_' . $number;
            $liattr = array('class' => join(' ', $liclasses), 'tabindex' => '-1', 'role' => 'treeitem') + $liexpandable;
            // class attribute on the div item which only contains the item content
            $divclasses = array('tree_item');
            if ($isbranch) {
                $divclasses[] = 'branch';
            } else {
                $divclasses[] = 'leaf';
            }
            if (!empty($item->classes) && count($item->classes)>0) {
                $divclasses[] = join(' ', $item->classes);
            }
            $divattr = array('class'=>join(' ', $divclasses));
            if (!empty($item->id)) {
                $divattr['id'] = $item->id;
            }
            $content = html_writer::tag('p', $content, $divattr) . $this->navigation_node($item, array(), $depth + 1);
            if (!empty($item->preceedwithhr) && $item->preceedwithhr===true) {
                $content = html_writer::empty_tag('hr') . $content;
            }
            $liattr['aria-labelledby'] = $nodetextid;
            $content = html_writer::tag('li', $content, $liattr);
            // Only render the proper site admin node if needed
            $adminnode = $item->key == 'siteadministration' && $item->type === navigation_node::TYPE_SITE_ADMIN;
            if ($adminnode && !$this->is_admin_tree_needed()) {
                $data = new stdClass();
                $data->siteadminlink = new moodle_url('/admin/index.php');
                $lis[] = $this->render_from_template('theme_shiny/administration_link', $data);
            } else {
                $lis[] = $content;
            }
        }

        if (count($lis)) {
            if (empty($attrs['role'])) {
                $attrs['role'] = 'group';
            }
            return html_writer::tag('ul', implode("\n", $lis), $attrs);
        } else {
            return '';
        }
    }
    public function search_form(moodle_url $formtarget, $searchvalue) {
        global $PAGE;
        if ($this->is_admin_tree_needed()) {
            return block_settings_renderer::search_form($formtarget, $searchvalue);
        } else {
            return '';
        }
    }
    private function is_admin_tree_needed() {
        $adminlayout = $this->page->pagelayout == 'admin';
        $adminpagetype = strpos($this->page->pagetype, 'admin-') == 0;
        $systemcontext = $this->page->context->contextlevel == CONTEXT_SYSTEM;
        return ($adminlayout || $adminpagetype) && $systemcontext;
    }
}
class theme_shiny_block_settings_renderer2 extends block_settings_renderer {
    protected function active_family(navigation_node $navigation) {
        $items = $navigation->children;
        if ($this->is_one_or_child_active($items)) {
            // Render these only.
            return $this->navigation_node($navigation);
        } else {
            // Keep going recursively
            $content = '';
            foreach ($items as $item) {
                $content .= $this->active_family($item);
            }
            return $content;
        }
    }
    private function is_one_or_child_active($items) {
        if ($this->is_one_active($items)) {
            return true;
        }
        foreach ($items as $item) {
            if ($this->is_one_active($item->children)) {
                return true;
            }
        }
        return false;
    }
    private function is_one_active($items) {
        foreach ($items as $item) {
            if ($item->isactive) {
                return true;
            }
        }
        return false;
    }
    protected function navigation_node(navigation_node $node, $attrs=array(), $depth = 1) {
        $items = $node->children;

        // exit if empty, we don't want an empty ul element
        if ($items->count()==0) {
            return '';
        }

        // array of nested li elements
        $lis = array();
        $number = 0;
        foreach ($items as $item) {
            $number++;
            if (!$item->display) {
                continue;
            }

            $isbranch = ($item->children->count()>0  || $item->nodetype==navigation_node::NODETYPE_BRANCH);
            $hasicon = (!$isbranch && $item->icon instanceof renderable);

            if ($isbranch) {
                $item->hideicon = true;
            }
            $content = $this->output->render($item);

            // this applies to the li item which contains all child lists too
            $liclasses = array($item->get_css_type());
            $liexpandable = array();
            if ($isbranch) {
                $liclasses[] = 'contains_branch';
                if (!$item->forceopen || (!$item->forceopen && $item->collapse) || ($item->children->count() == 0
                        && $item->nodetype == navigation_node::NODETYPE_BRANCH)) {
                    $liexpandable = array('aria-expanded' => 'false');
                } else {
                    $liexpandable = array('aria-expanded' => 'true');
                }
                if ($item->requiresajaxloading) {
                    $liexpandable['data-requires-ajax'] = 'true';
                    $liexpandable['data-loaded'] = 'false';
                }

            } else if ($hasicon) {
                $liclasses[] = 'item_with_icon';
            }
            if ($item->isactive === true) {
                $liclasses[] = 'current_branch';
            }
            $nodetextid = 'label_' . $depth . '_' . $number;
            $liattr = array('class' => join(' ', $liclasses), 'tabindex' => '-1', 'role' => 'treeitem') + $liexpandable;
            // class attribute on the div item which only contains the item content
            $divclasses = array('tree_item');
            if ($isbranch) {
                $divclasses[] = 'branch';
            } else {
                $divclasses[] = 'leaf';
            }
            if (!empty($item->classes) && count($item->classes)>0) {
                $divclasses[] = join(' ', $item->classes);
            }
            $divattr = array('class'=>join(' ', $divclasses));
            if (!empty($item->id)) {
                $divattr['id'] = $item->id;
            }
            $content = html_writer::tag('p', $content, $divattr) . $this->navigation_node($item, array(), $depth + 1);
            if (!empty($item->preceedwithhr) && $item->preceedwithhr===true) {
                $content = html_writer::empty_tag('hr') . $content;
            }
            $liattr['aria-labelledby'] = $nodetextid;
            $content = html_writer::tag('li', $content, $liattr);
            if ($node->contains_active_node()) {
                $lis[] = $content;
            }
        }

        if (count($lis)) {
            if (empty($attrs['role'])) {
                $attrs['role'] = 'group';
            }
            return html_writer::tag('ul', implode("\n", $lis), $attrs);
        } else {
            return '';
        }
    }
}

require_once($CFG->dirroot . "/blocks/navigation/renderer.php");

class theme_shiny_block_navigation_renderer extends block_navigation_renderer {
    /**
     * Returns the content of the navigation tree.
     *
     * @param global_navigation $navigation
     * @param int $expansionlimit
     * @param array $options
     * @return string $content
     */
    public function navigation_tree(global_navigation $navigation, $expansionlimit, array $options = array()) {
        $navigation->add_class('navigation_node');
        $navigationattrs = array(
            'class' => 'block_tree list',
            'role' => 'tree',
            'data-ajax-loader' => 'block_navigation/nav_loader');
        $content = $this->navigation_node(array($navigation), $navigationattrs, $expansionlimit, $options);
        if (isset($navigation->id) && !is_numeric($navigation->id) && !empty($content)) {
            $content = $this->output->box($content, 'block_tree_box', $navigation->id);
        }
        return $content;
    }
    /**
     * Produces a navigation node for the navigation tree
     *
     * @param navigation_node[] $items
     * @param array $attrs
     * @param int $expansionlimit
     * @param array $options
     * @param int $depth
     * @return string
     */
    protected function navigation_node($items, $attrs=array(), $expansionlimit=null, array $options = array(), $depth=1) {
        // Exit if empty, we don't want an empty ul element.
        if (count($items) === 0) {
            return '';
        }

        // Turn our navigation items into list items.
        $lis = array();
        $number = 0;
        foreach ($items as $item) {
            $number++;
            if (!$item->display && !$item->contains_active_node()) {
                continue;
            }
            $content = $item->get_content();
            $title = $item->get_title();

            $isexpandable = (empty($expansionlimit) || ($item->type > navigation_node::TYPE_ACTIVITY || $item->type < $expansionlimit) || ($item->contains_active_node() && $item->children->count() > 0));
            $isbranch = $isexpandable && ($item->children->count() > 0 || ($item->has_children() && (isloggedin() || $item->type <= navigation_node::TYPE_CATEGORY)));

            // Skip elements which have no content and no action - no point in showing them
            if (!$isexpandable && empty($item->action)) {
                continue;
            }

            $hasicon = ((!$isbranch || $item->type == navigation_node::TYPE_ACTIVITY || $item->type == navigation_node::TYPE_RESOURCE) && $item->icon instanceof renderable);

            if ($hasicon) {
                $icon = $this->output->render($item->icon);
                // Because an icon is being used we're going to wrap the actual content in a span.
                // This will allow designers to create columns for the content, as we've done in styles.css.
                $content = $icon . html_writer::span($content, 'item-content-wrap');
            } else {
                $icon = '';
            }

            if ($item->helpbutton !== null) {
                $content = trim($item->helpbutton).html_writer::tag('span', $content, array('class'=>'clearhelpbutton'));
            }

            if ($content === '') {
                continue;
            }

            $nodetextid = 'label_' . $depth . '_' . $number;
            $attributes = array('tabindex' => '-1', 'id' => $nodetextid);
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            if (is_string($item->action) || empty($item->action) ||
                    (($item->type === navigation_node::TYPE_CATEGORY || $item->type === navigation_node::TYPE_MY_CATEGORY) &&
                    empty($options['linkcategories']))) {
                $content = html_writer::tag('span', $content, $attributes);
            } else if ($item->action instanceof action_link) {
                //TODO: to be replaced with something else
                $link = $item->action;
                $link->text = $icon.html_writer::span($link->text, 'item-content-wrap');
                $link->attributes = array_merge($link->attributes, $attributes);
                $content = $this->output->render($link);
            } else if ($item->action instanceof moodle_url) {
                $content = html_writer::link($item->action, $content, $attributes);
            }

            // This applies to the li item which contains all child lists too.
            $liclasses = array($item->get_css_type(), 'depth_'.$depth);

            // Class attribute on the div item which only contains the item content.
            $divclasses = array('tree_item');

            $liexpandable = array();
            $lirole = array('role' => 'treeitem');
            if ($isbranch) {
                $liclasses[] = 'contains_branch';
                if ($depth == 1) {
                    $liexpandable = array(
                        'data-expandable' => 'false',
                        'data-collapsible' => 'false'
                    );
                } else {
                    $liexpandable = array(
                        'aria-expanded' => ($item->has_children() &&
                            (!$item->forceopen || $item->collapse)) ? "false" : "true");
                }

                if ($item->requiresajaxloading) {
                    $liexpandable['data-requires-ajax'] = 'true';
                    $liexpandable['data-loaded'] = 'false';
                    $liexpandable['data-node-id'] = $item->id;
                    $liexpandable['data-node-key'] = $item->key;
                    $liexpandable['data-node-type'] = $item->type;
                }

                $divclasses[] = 'branch';
            } else {
                $divclasses[] = 'leaf';
            }
            if ($hasicon) {
                // Add this class if the item has an icon, whether it is a branch or not.
                $liclasses[] = 'item_with_icon';
                $divclasses[] = 'hasicon';
            }
            if ($item->isactive === true) {
                $liclasses[] = 'current_branch';
            }
            if (!empty($item->classes) && count($item->classes)>0) {
                $divclasses[] = join(' ', $item->classes);
            }

            // Now build attribute arrays.
            $liattr = array('class' => join(' ', $liclasses)) + $liexpandable + $lirole;
            $divattr = array('class'=>join(' ', $divclasses));
            if (!empty($item->id)) {
                $divattr['id'] = $item->id;
            }

            // Create the structure.
            $content = html_writer::tag('p', $content, $divattr);
            if ($isexpandable) {
                $children = $this->navigation_node($item->children, array('role' => 'group'), $expansionlimit,
                    $options, $depth + 1);
                if (!empty($children)) {
                    $content = html_writer::empty_tag('hr') . $content;
                }
                $content .= $children;
            }
            if (!empty($item->preceedwithhr) && $item->preceedwithhr===true) {
                $content = html_writer::empty_tag('hr') . $content;
            }
            if ($depth == 1) {
                $liattr['tabindex'] = '0';
            }
            $liattr['aria-labelledby'] = $nodetextid;
            #$content = html_writer::tag('li', $content, $liattr);
            $lis[] = $content;
        }

        if (count($lis) === 0) {
            // There is still a chance, despite having items, that nothing had content and no list items were created.
            return '';
        }

        // We used to separate using new lines, however we don't do that now, instead we'll save a few chars.
        // The source is complex already anyway.
        #return html_writer::tag('ul', implode('', $lis), $attrs);
        return implode('', $lis);
    }

}
/**
 * Override the renderer for block navigation
 *
 * @copyright 2016 John Okely <john@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_shiny_block_navigation_renderer2 extends block_navigation_renderer {
    /**
     * Returns the content of the navigation tree.
     *
     * @param global_navigation $navigation
     * @param int $expansionlimit
     * @param array $options
     * @return string $content
     */
    public function navigation_tree(global_navigation $navigation, $expansionlimit, array $options = array()) {
        $navigation->add_class('navigation_node');
        $navigationattrs = array(
            'class' => 'block_tree list',
            'role' => 'tree',
            'data-ajax-loader' => 'block_navigation/nav_loader');
        $content = $this->active_family(array($navigation), $navigationattrs, $expansionlimit, $options);
        if (isset($navigation->id) && !is_numeric($navigation->id) && !empty($content)) {
            $content = $this->output->box($content, 'block_tree_box', $navigation->id);
        }
        return $content;
    }
    /**
     * Produces a navigation node for the navigation tree
     *
     * @param navigation_node[] $items
     * @param array $attrs
     * @param int $expansionlimit
     * @param array $options
     * @param int $depth
     * @return string
     */
    protected function active_family($items, $attrs=array(), $expansionlimit=null, array $options = array(), $depth=1) {
        if ($this->is_one_or_child_active($items)) {
            // Render these only.
            return $this->navigation_node($items, $attrs, $expansionlimit, $options, 1);
        } else {
            // Keep going recursively
            $content = '';
            foreach ($items as $item) {
                $content .= $this->active_family($item->children, $attrs, $expansionlimit, $options, $depth + 1);
            }
            return $content;
        }
    }
    private function is_one_or_child_active($items) {
        if ($this->is_one_active($items)) {
            return true;
        }
        foreach ($items as $item) {
            if ($this->is_one_active($item->children)) {
                return true;
            }
        }
        return false;
    }
    private function is_one_active($items) {
        foreach ($items as $item) {
            if ($item->isactive) {
                return true;
            }
        }
        return false;
    }
    /**
     * Produces a navigation node for the navigation tree
     *
     * @param navigation_node[] $items
     * @param array $attrs
     * @param int $expansionlimit
     * @param array $options
     * @param int $depth
     * @return string
     */
    protected function navigation_node($items, $attrs=array(), $expansionlimit=null, array $options = array(), $depth=1) {
        // Exit if empty, we don't want an empty ul element.
        if (count($items) === 0) {
            return '';
        }

        // Turn our navigation items into list items.
        $lis = array();
        $number = 0;
        foreach ($items as $item) {
            $number++;
            if (!$item->display && !$item->contains_active_node()) {
                continue;
            }
            $content = $item->get_content();
            $title = $item->get_title();

            $isexpandable = (empty($expansionlimit) || ($item->type > navigation_node::TYPE_ACTIVITY || $item->type < $expansionlimit) || ($item->contains_active_node() && $item->children->count() > 0));
            $isbranch = $isexpandable && ($item->children->count() > 0 || ($item->has_children() && (isloggedin() || $item->type <= navigation_node::TYPE_CATEGORY)));

            // Skip elements which have no content and no action - no point in showing them
            if (!$isexpandable && empty($item->action)) {
                continue;
            }

            $hasicon = ((!$isbranch || $item->type == navigation_node::TYPE_ACTIVITY || $item->type == navigation_node::TYPE_RESOURCE) && $item->icon instanceof renderable);

            if ($hasicon) {
                $icon = $this->output->render($item->icon);
                // Because an icon is being used we're going to wrap the actual content in a span.
                // This will allow designers to create columns for the content, as we've done in styles.css.
                $content = $icon . html_writer::span($content, 'item-content-wrap');
            } else {
                $icon = '';
            }

            if ($item->helpbutton !== null) {
                $content = trim($item->helpbutton).html_writer::tag('span', $content, array('class'=>'clearhelpbutton'));
            }

            if ($content === '') {
                continue;
            }

            $nodetextid = 'label_' . $depth . '_' . $number;
            $attributes = array('tabindex' => '-1', 'id' => $nodetextid);
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            if (is_string($item->action) || empty($item->action) ||
                    (($item->type === navigation_node::TYPE_CATEGORY || $item->type === navigation_node::TYPE_MY_CATEGORY) &&
                    empty($options['linkcategories']))) {
                $content = html_writer::tag('span', $content, $attributes);
            } else if ($item->action instanceof action_link) {
                //TODO: to be replaced with something else
                $link = $item->action;
                $link->text = $icon.html_writer::span($link->text, 'item-content-wrap');
                $link->attributes = array_merge($link->attributes, $attributes);
                $content = $this->output->render($link);
            } else if ($item->action instanceof moodle_url) {
                $content = html_writer::link($item->action, $content, $attributes);
            }

            // This applies to the li item which contains all child lists too.
            $liclasses = array($item->get_css_type(), 'depth_'.$depth);

            // Class attribute on the div item which only contains the item content.
            $divclasses = array('tree_item');

            $liexpandable = array();
            $lirole = array('role' => 'treeitem');
            if ($isbranch) {
                $liclasses[] = 'contains_branch';
                if ($depth == 1) {
                    $liexpandable = array(
                        'data-expandable' => 'false',
                        'data-collapsible' => 'false'
                    );
                } else {
                    $liexpandable = array(
                        'aria-expanded' => ($item->has_children() &&
                            (!$item->forceopen || $item->collapse)) ? "false" : "true");
                }

                if ($item->requiresajaxloading) {
                    $liexpandable['data-requires-ajax'] = 'true';
                    $liexpandable['data-loaded'] = 'false';
                    $liexpandable['data-node-id'] = $item->id;
                    $liexpandable['data-node-key'] = $item->key;
                    $liexpandable['data-node-type'] = $item->type;
                }

                $divclasses[] = 'branch';
            } else {
                $divclasses[] = 'leaf';
            }
            if ($hasicon) {
                // Add this class if the item has an icon, whether it is a branch or not.
                $liclasses[] = 'item_with_icon';
                $divclasses[] = 'hasicon';
            }
            if ($item->isactive === true) {
                $liclasses[] = 'current_branch';
            }
            if (!empty($item->classes) && count($item->classes)>0) {
                $divclasses[] = join(' ', $item->classes);
            }

            // Now build attribute arrays.
            $liattr = array('class' => join(' ', $liclasses)) + $liexpandable + $lirole;
            $divattr = array('class'=>join(' ', $divclasses));
            if (!empty($item->id)) {
                $divattr['id'] = $item->id;
            }

            // Create the structure.
            $content = html_writer::tag('p', $content, $divattr);
            if ($isexpandable) {
                $content .= $this->navigation_node($item->children, array('role' => 'group'), $expansionlimit,
                    $options, $depth + 1);
            }
            if (!empty($item->preceedwithhr) && $item->preceedwithhr===true) {
                $content = html_writer::empty_tag('hr') . $content;
            }
            if ($depth == 1) {
                $liattr['tabindex'] = '0';
            }
            $liattr['aria-labelledby'] = $nodetextid;
            $content = html_writer::tag('li', $content, $liattr);
            $lis[] = $content;
        }

        if (count($lis) === 0) {
            // There is still a chance, despite having items, that nothing had content and no list items were created.
            return '';
        }

        // We used to separate using new lines, however we don't do that now, instead we'll save a few chars.
        // The source is complex already anyway.
        return html_writer::tag('ul', implode('', $lis), $attrs);
    }

}

class theme_shiny_core_user_myprofile_renderer extends \core_user\output\myprofile\renderer {
    public function render_tree(core_user\output\myprofile\tree $tree) {
        return $this->context_header() . \core_user\output\myprofile\renderer::render_tree($tree);
    }
}
