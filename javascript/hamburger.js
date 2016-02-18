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
 * Open and closable hamburger
 *
 * @module     theme_shiny/hamburger
 * @package    theme_shiny
 * @copyright  2016 John Okely <john@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require(['jquery'], function($) {
    var SELECTORS = {
        HAMBURGER: '#navbar-hamburger',
        MENU: '#block-region-side-pre',
        CONTENT: '#region-main'
    };
    var Hamburger = function(hamburger, menu, content) {
        this.hamburger = $(hamburger);
        this.menu = $(menu);
        this.content = $(content);
        this.expanded = false;
        if (localStorage.getItem("hamburger_expanded") == 'true') {
            this.expanded = true;
            this.expand();
        }

        this.bindEventHandlers();
    };
    Hamburger.prototype.handleClick = function(hamburger, e) {
        if (this.expanded) {
            this.collapse();
        } else {
            this.expand();
        }
        this.expanded = !this.expanded;
        localStorage.setItem("hamburger_expanded", this.expanded);
        e.stopPropagation();
        return true;
    }
    Hamburger.prototype.expand = function() {
        this.content.addClass('span9');
        this.content.removeClass('span12');
        this.menu.removeClass('collapsed');
    }
    Hamburger.prototype.collapse = function() {
        this.content.addClass('span12');
        this.content.removeClass('span9');
        this.menu.addClass('collapsed');
    }
    Hamburger.prototype.bindEventHandlers = function() {
        var thisObj = this;

        // Bind event handlers to the tree items. Use event delegates to allow
        // for dynamically loaded parts of the tree.
        this.hamburger.on({
            click: function(e) { return thisObj.handleClick($(this), e); },
        });
    };
    var hb = new Hamburger(SELECTORS.HAMBURGER, SELECTORS.MENU, SELECTORS.CONTENT);
});
