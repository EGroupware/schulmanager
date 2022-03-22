"use strict";
/**
 * EGroupware - Schulmanager - Javascript UI
 *
 * @link http://www.egroupware.org
 * @package resources
 * @author Axel Wild
 * @copyright (c) todo
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id: app.js todo
 */
var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
Object.defineProperty(exports, "__esModule", { value: true });
exports.SchulmanagerApp = void 0;
require("jquery");
require("jqueryui");
require("../jsapi/egw_global");
require("../etemplate/et2_types");
var egw_app_1 = require("../../api/js/jsapi/egw_app");
//import {et2_nextmatch} from "../../api/js/etemplate/et2_extension_nextmatch";
var etemplate2_1 = require("../../api/js/etemplate/etemplate2");
// Object.defineProperty(exports, "__esModule", { value: true });
var SchulmanagerApp = /** @class */ (function (_super) {
    __extends(SchulmanagerApp, _super);
    function SchulmanagerApp() {
        var _this = _super.call(this, 'schulmanager') || this;
        _this.appname = 'schulmanager';
        return _this;
    }
    /**
     * This function is called when the etemplate2 object is loaded
     * and ready.  If you must store a reference to the et2 object,
     * make sure to clean it up in destroy().
     *
     * @param et2 etemplate2 Newly ready object
     * @param string name
     */
    SchulmanagerApp.prototype.et2_ready = function (et2, name) {
        // call parent
        _super.prototype.et2_ready.call(this, et2, name);
        if (name == 'schulmanager.notenmanager.index') {
            this.header_change();
        }
        if (name == 'schulmanager.notenmanager.edit') {
            this.header_change();
        }
        if (name == 'schulmanager.calendar.index') {
            this.cal_header_change();
            this.cal_hide_items();
        }
    };
    /**
     * laden der gewichtungen beim ersten Laden des Templates
     * @param {type} _action
     * @returns {undefined}
     */
    SchulmanagerApp.prototype.header_change = function () {
        var et2 = this.et2;
        var func = 'schulmanager.notenmanager_ui.ajax_getGewichtungen';
        var query = '';
        this.egw.json(func, [query], function (totals) {
            for (var key in totals) {
                var widget = et2.getWidgetById(key);
                if (widget) {
                    widget.set_value(totals[key]);
                }
            }
        }).sendRequest(true);
    };
    // export notenbuch
    SchulmanagerApp.prototype.exportpdf_nb = function (_id, _widget) {
        this.egw.loading_prompt('schulmanager', true, egw.lang('please wait...'));
        var req = new XMLHttpRequest();
        req.open("POST", egw.link('/index.php', 'menuaction=schulmanager.schulmanager_download_ui.exportpdf_nb'), true);
        req.responseType = "blob";
        req.onreadystatechange = function () {
            if (req.readyState === 4 && req.status === 200) {
                //var filename = "PdfName-" + new Date().getTime() + ".pdf";
                var header = req.getResponseHeader('Content-Disposition');
                var startIndex = header.indexOf("filename=") + 10;
                var endIndex = header.length - 1;
                var filename = header.substring(startIndex, endIndex);
                if (typeof window.chrome !== 'undefined') {
                    // Chrome version
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(req.response);
                    //link.download = "PdfName-" + new Date().getTime() + ".pdf";
                    link.download = filename;
                    link.click();
                }
                else if (typeof window.navigator.msSaveBlob !== 'undefined') {
                    // IE version
                    var blob = new Blob([req.response], { type: 'application/pdf' });
                    window.navigator.msSaveBlob(blob, filename);
                }
                else {
                    // Firefox version
                    var file = new File([req.response], filename, { type: 'application/force-download' });
                    window.open(URL.createObjectURL(file));
                }
            }
            egw.loading_prompt('schulmanager', false);
        };
        req.send();
    };
    /**
     * test download pdf
     * @param {type} _id
     * @param {type} _widget
     * @returns {undefined}
     */
    SchulmanagerApp.prototype.exportpdf_kv = function (_id, _widget) {
        /**
        // running DO NOT DELETE
        egw.loading_prompt('schulmanager',true,egw.lang('please wait...'),this.baseDiv, egwIsMobile()?'horizental':'spinner');
        var a = document.createElement('a');
        var url = egw.link('/index.php','menuaction=schulmanager.schulmanager_download_ui.exportpdf_kv');
        a.href = url;
        a.download = 'filename.pdf';
        document.body.append(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
        alert("Download wird im Hintergrund gestartet!")
        egw.loading_prompt('schulmanager',false);
        */
        this.egw.loading_prompt('schulmanager', true, egw.lang('please wait...'));
        var req = new XMLHttpRequest();
        req.open("POST", egw.link('/index.php', 'menuaction=schulmanager.schulmanager_download_ui.exportpdf_kv'), true);
        req.responseType = "blob";
        req.onreadystatechange = function () {
            if (req.readyState === 4 && req.status === 200) {
                //var filename = "PdfName-" + new Date().getTime() + ".pdf";
                var header = req.getResponseHeader('Content-Disposition');
                var startIndex = header.indexOf("filename=") + 10;
                var endIndex = header.length - 1;
                var filename = header.substring(startIndex, endIndex);
                if (typeof window.chrome !== 'undefined') {
                    // Chrome version
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(req.response);
                    //link.download = "PdfName-" + new Date().getTime() + ".pdf";
                    link.download = filename;
                    link.click();
                }
                else if (typeof window.navigator.msSaveBlob !== 'undefined') {
                    // IE version
                    var blob = new Blob([req.response], { type: 'application/pdf' });
                    window.navigator.msSaveBlob(blob, filename);
                }
                else {
                    // Firefox version
                    var file = new File([req.response], filename, { type: 'application/force-download' });
                    window.open(URL.createObjectURL(file));
                }
            }
            egw.loading_prompt('schulmanager', false);
        };
        req.send();
    };
    // export notenbuch
    SchulmanagerApp.prototype.exportpdf_calm = function (_id, _widget) {
        var egw = this.egw;
        egw.loading_prompt('schulmanager', true, egw.lang('please wait...'));
        var req = new XMLHttpRequest();
        req.open("POST", egw.link('/index.php', 'menuaction=schulmanager.schulmanager_download_ui.exportpdf_calm'), true);
        req.responseType = "blob";
        req.onreadystatechange = function () {
            if (req.readyState === 4 && req.status === 200) {
                //var filename = "PdfName-" + new Date().getTime() + ".pdf";
                var header = req.getResponseHeader('Content-Disposition');
                var startIndex = header.indexOf("filename=") + 10;
                var endIndex = header.length - 1;
                var filename = header.substring(startIndex, endIndex);
                if (typeof window.chrome !== 'undefined') {
                    // Chrome version
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(req.response);
                    link.download = filename;
                    link.click();
                }
                else if (typeof window.navigator.msSaveBlob !== 'undefined') {
                    // IE version
                    var blob = new Blob([req.response], { type: 'application/pdf' });
                    window.navigator.msSaveBlob(blob, filename);
                }
                else {
                    // Firefox version
                    var file = new File([req.response], filename, { type: 'application/force-download' });
                    window.open(URL.createObjectURL(file));
                }
            }
            egw.loading_prompt('schulmanager', false);
        };
        req.send();
    };
    /**
     * laden der weekdays
     * @param {type} _action
     * @returns {undefined}
     */
    SchulmanagerApp.prototype.cal_header_change = function () {
        var et2 = this.et2;
        var func = 'schulmanager.schulmanager_cal_ui.ajax_getWeekdays';
        var query = '';
        this.egw.json(func, [query], function (result) {
            //var widget = et2.getWidgetById('glnw_1_0');
            //widget.set_value(totals['glnw_1_0']);
            for (var key in result['nm_header']) {
                //alert(key);
                var widget = et2.getWidgetById(key);
                if (widget) {
                    widget.getDOMNode().childNodes[0].innerHTML = result['nm_header'][key]['nr'];
                    widget.getDOMNode().childNodes[1].innerHTML = result['nm_header'][key]['name'];
                    widget.parentNode.parentNode.classList.remove('sm_cal_saso');
                    widget.parentNode.parentNode.classList.remove('sm_cal_mofr');
                    widget.parentNode.parentNode.classList.add(result['nm_header'][key]['class']);
                    if (result['nm_header'][key]['class'] == 'sm_cal_saso' && widget.getDOMNode().childNodes[2]) {
                        widget.getDOMNode().childNodes[2].style.display = "none";
                    }
                }
            }
        }).sendRequest(true);
    };
    SchulmanagerApp.prototype.cal_hide_items = function () {
        egw.css(".sm_cal_hidden", "display:none");
    };
    SchulmanagerApp.prototype.cal_focus_item = function (_action, widget) {
        //alert(widget.getDOMNode());
        var _send = function () {
            egw().json('schulmanager.schulmanager_cal_ui.ajax_editCalEvent', [
                widget.id
            ], 
            // Remove loading spinner
            function (result) {
                jQuery(_action).blur();
                //alert(result['test']);
            }).sendRequest(true);
        };
        _send();
    };
    SchulmanagerApp.prototype.calEditMultiple = function (_action, widget) {
        //alert(widget.id);
        var _send = function () {
            egw().json('schulmanager.schulmanager_cal_ui.ajax_editCalEvent', [
                widget.id,
                true
            ], 
            // Remove loading spinner
            function (result) {
                jQuery(_action).blur();
                //alert(result['test']);
            }).sendRequest(true);
        };
        _send();
    };
    SchulmanagerApp.prototype.calDeleteEvent = function (_action, widget) {
        //alert(widget[0].id);
        var _send = function () {
            egw().json('schulmanager.schulmanager_cal_ui.ajax_deleteEvent', [
                widget[0].id
            ], 
            // Remove loading spinner
            function (result) {
                egw(window).refresh(result['msg'], 'schulmanager', 'schulmanager-calendar-editevent', 'update');
                var nm = etemplate2_1.etemplate2.getById('schulmanager-calendar-editevent').widgetContainer.getWidgetById('nm');
                nm.refresh(null, 'update');
            }).sendRequest(true);
        };
        _send();
    };
    SchulmanagerApp.prototype.add_list_event = function (widget, prefix) {
        var et2 = this.et2;
        //alert(prefix.concat('sm_type_options_list'));
        var type_id = prefix.concat('sm_type_options_list');
        var fach_id = prefix.concat('sm_fach_options_list');
        var user_id = prefix.concat('sm_user_list');
        //alert(type_id);
        var type = document.getElementById(type_id).value;
        var fach = document.getElementById(fach_id).value;
        var user = null;
        if (document.getElementById(user_id)) {
            user = document.getElementById(user_id).value;
        }
        else {
            user_id = prefix.concat('sm_activeuserID');
            user = document.getElementById(user_id).value;
        }
        //alert(type);
        //alert(fach);
        //alert(user);
        var _send = function () {
            egw().json('schulmanager.schulmanager_cal_ui.ajax_addEventToList', [
                type,
                fach,
                user
            ], 
            // Remove loading spinner
            function (result) {
                egw(window).refresh(result['msg'], 'schulmanager', 'schulmanager-calendar-editevent', 'update');
                var nm = etemplate2_1.etemplate2.getById('schulmanager-calendar-editevent').widgetContainer.getWidgetById('nm');
                nm.refresh(null, 'update');
            }).sendRequest(true);
        };
        _send();
    };
    SchulmanagerApp.prototype.calShowAddEvent = function (action, selected) {
        //alert("test");
        jQuery('table.addnewevent').css('display', 'inline');
    };
    SchulmanagerApp.prototype.nmEditGew = function (_action, widget) {
        //alert("test");
        //alert(widget.id);
        jQuery('table.editgew').css('display', 'inline');
        //	jQuery('table.editgew-content').css('display','inline');
    };
    SchulmanagerApp.prototype.changeNote = function (action, _senders) {
        var et2 = this.et2;
        var func = 'schulmanager.notenmanager_ui.ajax_noteModified';
        var noteKey = action.name;
        var noteVal = action.value;
        if (action.type == "checkbox") {
            if (action.checked) {
                noteVal = 1;
            }
            else {
                noteVal = 0;
            }
        }
        this.egw.json(func, [noteKey, noteVal], function (result) {
            jQuery(action).addClass('schulmanager_note_changed');
            for (var key in result) {
                var cssAvgKey = 'schulmanager-notenmanager-edit_' + key + '[-1][note]';
                var widget = document.getElementById(cssAvgKey);
                if (widget) {
                    widget.value = result[key]['[-1][note]'];
                    if (action.id == cssAvgKey) {
                        // reset css class if this input value has been modified
                        jQuery(action).removeClass('nm_avg_manuell');
                        jQuery(action).removeClass('nm_avg_auto');
                        jQuery(action).addClass(result[key]['avgclass']);
                    }
                }
            }
        }).sendRequest(true);
    };
    SchulmanagerApp.prototype.changeGew = function (action, senders) {
        var et2 = this.et2;
        var func = 'schulmanager.notenmanager_ui.ajax_gewModified';
        var gewKey = action.name;
        var gewVal = action.value;
        this.egw.json(func, [gewKey, gewVal], function (result) {
            jQuery(action).addClass('schulmanager_note_changed');
            for (var key in result) {
                var cssAvgKey = 'schulmanager-notenmanager-edit_' + key + '[-1][note]';
                var widget = document.getElementById(cssAvgKey);
                if (widget) {
                    widget.value = result[key]['[-1][note]'];
                }
            }
        }).sendRequest(true);
    };
    SchulmanagerApp.prototype.changeGewAllModified = function (_action, _senders) {
        var et2 = this.et2;
        var func = 'schulmanager.notenmanager_ui.ajax_gewAllModified';
        var noteKey = _action.name;
        var noteVal = _action.value;
        if (_action.type == "checkbox") {
            if (_action.checked) {
                noteVal = 1;
            }
            else {
                noteVal = 0;
            }
        }
        this.egw.json(func, [noteKey, noteVal], function (result) {
            jQuery(_action).addClass('schulmanager_note_changed');
            for (var key in result) {
                var cssAvgKey = 'schulmanager-notenmanager-edit_' + key + '[-1][note]';
                var cssAltBKey = 'schulmanager-notenmanager-edit_' + key + '[-1][checked]';
                var widget = document.getElementById(cssAvgKey);
                var widgetAltB = document.getElementById(cssAltBKey);
                if (widget) {
                    widget.value = result[key]['[-1][note]'];
                    if (_action.id == cssAvgKey) {
                        jQuery(_action).removeClass('nm_avg_manuell');
                        jQuery(_action).removeClass('nm_avg_auto');
                        jQuery(_action).addClass(result[key]['avgclass']);
                    }
                }
                else if (widgetAltB) {
                    widgetAltB.checked = result[key]['[-1][checked]'];
                }
            }
        }).sendRequest(true);
    };
    /**
     * selectes teacher for a new substitution changed, reload list
     */
    SchulmanagerApp.prototype.subs_TeacherChanged = function (_action, _senders) {
        var et2 = this.et2;
        var func = 'schulmanager.schulmanager_substitution_ui.ajax_getTeacherLessonList';
        var teacher_id = _action.value;
        this.egw.json(func, [teacher_id], function (result) {
            var widget = document.getElementById('schulmanager-substitution_add_lesson_list');
            if (widget) {
                var length = widget.options.length;
                for (var i = length - 1; i >= 0; i--) {
                    widget.options[i] = null;
                }
                for (var key in result) {
                    var opt = document.createElement("option");
                    opt.value = key;
                    opt.text = result[key];
                    widget.options.add(opt);
                }
            }
        }).sendRequest(true);
    };
    return SchulmanagerApp;
}(egw_app_1.EgwApp));
exports.SchulmanagerApp = SchulmanagerApp;
app.classes.schulmanager = SchulmanagerApp;
