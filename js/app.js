"use strict";
/**
 * EGroupware Schulmanager
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */
var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
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
var egw_app_1 = require("../../api/js/jsapi/egw_app");
var etemplate2_1 = require("../../api/js/etemplate/etemplate2");
var et2_extension_nextmatch_1 = require("../../api/js/etemplate/et2_extension_nextmatch");
var et2_widget_dialog_1 = require("../../api/js/etemplate/et2_widget_dialog");
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
        if (name == 'schulmanager.notenmanager.notendetails') {
            this.header_change();
        }
        if (name == 'schulmanager.schuelerview') {
            this.onSchuelerViewKlasseChanged();
        }
    };
    /**
     * laden der gewichtungen beim ersten Laden des Templates
     * @param {type} _action
     * @returns {undefined}
     */
    SchulmanagerApp.prototype.header_change = function () {
        var et2 = this.et2;
        var func = 'schulmanager.schulmanager_ui.ajax_getGewichtungen';
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
        this.egw.loading_prompt('schulmanager', true, this.egw.lang('please wait...'));
        var $a = jQuery(document.createElement('a')).appendTo('body').hide();
        var url = window.egw.webserverUrl + '/index.php?menuaction=schulmanager.schulmanager_download_ui.exportpdf_nb';
        $a.prop('href', url);
        $a.prop('download', '');
        $a[0].click();
        $a.remove();
        this.egw.loading_prompt('schulmanager', false);
    };
    /**
     * download all grades in all subjects of all students as pdf
     * @param {type} _id
     * @param {type} _widget
     * @returns {undefined}
     */
    SchulmanagerApp.prototype.exportpdf_kv = function (_id, _widget) {
        var id = _widget.id;
        var params = 'mode=stud';
        if (id == "button[pdfexportteacherzz]") {
            params = 'mode=teacher_zz';
        }
        else if (id == "button[pdfexportteacherjz]") {
            params = 'mode=teacher_jz';
        }
        var modal = document.getElementById("schulmanager-notenmanager-klassenview_showexportmodal");
        modal.style.display = "none";
        this.egw.loading_prompt('schulmanager', true, egw.lang('please wait...'));
        var $a = jQuery(document.createElement('a')).appendTo('body').hide();
        var url = window.egw.webserverUrl + '/index.php?menuaction=schulmanager.schulmanager_download_ui.exportpdf_kv&' + params;
        $a.prop('href', url);
        $a.prop('download', '');
        $a[0].click();
        $a.remove();
        egw.loading_prompt('schulmanager', false);
    };
    /**
     * reload klassleiter before pdf export
     * @param _id
     * @param _widget
     */
    SchulmanagerApp.prototype.exportpdf_nbericht_prepare = function (_id, _widget) {
        var modal = document.getElementById("schulmanager-notenmanager-klassenview_showexportmodal");
        modal.style.display = "block";
        var func = 'schulmanager.schulmanager_ui.ajax_nbericht_prepare';
        this.egw.json(func, [], function (result) {
            var widget = document.getElementById('schulmanager-notenmanager-klassenview_klassleiter');
            if (widget) {
                var length_1 = widget.options.length;
                for (var i = length_1 - 1; i >= 0; i--) {
                    widget.options[i] = null;
                }
                for (var key in result['klassleiter']) {
                    var opt = document.createElement("option");
                    opt.value = key;
                    opt.text = result['klassleiter'][key];
                    widget.options.add(opt);
                }
            }
        }).sendRequest(true);
    };
    /**
     * download all grades in all subjects of all students as pdf handout for students
     * @param {type} _id
     * @param {type} _widget
     * @returns {undefined}
     */
    SchulmanagerApp.prototype.exportpdf_nbericht = function (_id, _widget) {
        var modal = document.getElementById("schulmanager-notenmanager-klassenview_showexportmodal");
        modal.style.display = "none";
        this.egw.loading_prompt('schulmanager', true, this.egw.lang('please wait...'));
        var showReturn = document.getElementById('schulmanager-notenmanager-klassenview_add_return_block').checked;
        var showSigned = document.getElementById('schulmanager-notenmanager-klassenview_add_signed_block').checked;
        var signerWidget = document.getElementById('schulmanager-notenmanager-klassenview_klassleiter');
        var signerid = signerWidget.value;
        var $a = jQuery(document.createElement('a')).appendTo('body').hide();
        var url = window.egw.webserverUrl + '/index.php?menuaction=schulmanager.schulmanager_download_ui.exportpdf_nbericht&showReturnInfo=' + showReturn + '&signed=' + showSigned + '&signerid=' + signerid;
        $a.prop('href', url);
        $a.prop('download', '');
        $a[0].click();
        $a.remove();
        this.egw.loading_prompt('schulmanager', false);
    };
    /**
     * export notenbuch
     * @param _id
     * @param _widget
     */
    SchulmanagerApp.prototype.exportpdf_calm = function (_id, _widget) {
        var egw = this.egw;
        egw.loading_prompt('schulmanager', true, egw.lang('please wait...'));
        var $a = jQuery(document.createElement('a')).appendTo('body').hide();
        var url = window.egw.webserverUrl + '/index.php?menuaction=schulmanager.schulmanager_download_ui.exportpdf_calm';
        $a.prop('href', url);
        $a.prop('download', '');
        $a[0].click();
        $a.remove();
        this.egw.loading_prompt('schulmanager', false);
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
            for (var key in result['nm_header']) {
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
        var type_id = prefix.concat('sm_type_options_list');
        var fach_id = prefix.concat('sm_fach_options_list');
        var user_id = prefix.concat('sm_user_list');
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
        jQuery('table.addnewevent').css('display', 'inline');
    };
    SchulmanagerApp.prototype.nmEditGew = function (_action, widget) {
        jQuery('table.editgew').css('display', 'inline');
    };
    SchulmanagerApp.prototype.changeNote = function (action, _senders) {
        var tokenDiv = document.getElementById('schulmanager-notenmanager-edit_token');
        var inputinfo_date = document.getElementById('schulmanager-notenmanager-edit_date').firstChild;
        var inputinfo_type = document.getElementById('schulmanager-notenmanager-edit_notgebart');
        var inputinfo_desc = document.getElementById('schulmanager-notenmanager-edit_desc');
        var func = 'schulmanager.schulmanager_ui.ajax_noteModified';
        var noteKey = action.name;
        var noteVal = action.value;
        var token = tokenDiv.value;
        var note_date = inputinfo_date.value;
        var note_type = inputinfo_type.value;
        var note_desc = inputinfo_desc.value;
        if (action.type == "checkbox") {
            if (action.checked) {
                noteVal = 1;
            }
            else {
                noteVal = 0;
            }
        }
        this.egw.json(func, [noteKey, noteVal, token, note_date, note_type, note_desc], function (result) {
            jQuery(action).addClass('schulmanager_note_changed');
            for (var key in result) {
                if (key == 'error_msg') {
                    alert("Error:\n\t" + result[key]);
                    break;
                }
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
        var func = 'schulmanager.schulmanager_ui.ajax_gewModified';
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
        var func = 'schulmanager.schulmanager_ui.ajax_gewAllModified';
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
                var length_2 = widget.options.length;
                for (var i = length_2 - 1; i >= 0; i--) {
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
    /**
     * AJAX loading student
     * @param _action
     * @param _senders
     */
    SchulmanagerApp.prototype.onDetailsNote = function (_action, _senders) {
        var modal = document.getElementById("schulmanager-notenmanager-index_showdetailsmodal");
        modal.style.display = "block";
        var instance = this;
        var stud_id = _senders[0]._index;
        var func = 'schulmanager.schulmanager_ui.ajax_getStudentDetails';
        this.egw.json(func, [stud_id], function (result) {
            var modal = document.getElementById("schulmanager-notenmanager-index");
            modal.style.display = "block";
            for (var key in result) {
                // noten
                if (key == 'details_noten') {
                    instance.updateDetailsNoten(key, result, 'schulmanager-notenmanager-index_');
                }
                else {
                    var widget_id = 'schulmanager-notenmanager-index_' + key;
                    var widget = document.getElementById(widget_id);
                    if (widget) {
                        widget.innerText = result[key];
                    }
                }
            }
            jQuery('#schulmanager-notenmanager-index_schulmanager-notenmanager-details').css('display', 'inline');
        }).sendRequest(true);
    };
    /**
     * AJAX loading student
     * @param _action
     * @param _senders
     */
    SchulmanagerApp.prototype.onContactData = function (_action, _senders) {
        var modal = document.getElementById("schulmanager-notenmanager-index_showcontactmodal");
        modal.style.display = "block";
        var instance = this;
        var stud_id = _senders[0]._index;
        var func = 'schulmanager.schulmanager_ui.ajax_getStudentContact';
        this.egw.json(func, [stud_id], function (result) {
            var modal = document.getElementById("schulmanager-notenmanager-index");
            modal.style.display = "block";
            instance.tableUpdate("schulmanager-notenmanager-index_grid-sko", result['sko_nm_rows']);
            instance.tableUpdate("schulmanager-notenmanager-index_grid-san", result['san_nm_rows']);
            delete result['sko_nm_rows'];
            delete result['san_nm_rows'];
            for (var key in result) {
                var widget_id = 'schulmanager-notenmanager-index_' + key;
                var widget = document.getElementById(widget_id);
                if (widget) {
                    widget.innerText = result[key];
                }
            }
            jQuery('#schulmanager-notenmanager-index_schulmanager-contact').css('display', 'inline');
        }).sendRequest(true);
    };
    /**
     * select students for a new class changed, reload list
     */
    SchulmanagerApp.prototype.onDetailsKlasseChanged = function (_action, _senders) {
        var instance = this;
        var egw = this.egw;
        this.egw.loading_prompt('schulmanager', true, egw.lang('please wait...'));
        var et2 = this.et2;
        var func = 'schulmanager.schulmanager_ui.ajax_DetailsKlasseChanged';
        var klasse_id = _action.value;
        this.egw.json(func, [klasse_id], function (result) {
            // update schueler select
            var widget = document.getElementById('schulmanager-notenmanager-notendetails_select_schueler');
            if (widget) {
                var length_3 = widget.options.length;
                for (var i = length_3 - 1; i >= 0; i--) {
                    widget.options[i] = null;
                }
                for (var key in result['select_schueler']) {
                    var opt = document.createElement("option");
                    opt.value = key;
                    opt.text = result['select_schueler'][key];
                    widget.options.add(opt);
                }
            }
            delete (result['select_schueler']);
            for (var key in result) {
                if (key == 'details_noten') {
                    instance.updateDetailsNoten(key, result, 'schulmanager-notenmanager-notendetails_');
                }
                else {
                    var widgetItem = document.getElementById('schulmanager-notenmanager-notendetails_' + key);
                    if (widgetItem) {
                        widgetItem.innerText = result[key];
                    }
                }
            }
            egw.loading_prompt('schulmanager', false);
        }).sendRequest(true);
    };
    /**
     * selectes teacher for a new substitution changed, reload list
     */
    SchulmanagerApp.prototype.onDetailsSchuelerChanged = function (_action, _senders) {
        var instance = this;
        //this.egw.loading_prompt('schulmanager',true,egw.lang('please wait...'));
        var et2 = this.et2;
        var func = 'schulmanager.schulmanager_ui.ajax_DetailsSchuelerChanged';
        var schueler_id = _action.value;
        this.egw.json(func, [schueler_id], function (result) {
            for (var key in result) {
                if (key == 'details_noten') {
                    instance.updateDetailsNoten(key, result, 'schulmanager-notenmanager-notendetails_');
                }
                else {
                    var widgetItem = document.getElementById('schulmanager-notenmanager-notendetails_' + key);
                    if (widgetItem) {
                        widgetItem.innerText = result[key];
                    }
                    else {
                        //alert(key);
                    }
                }
            }
            //egw.loading_prompt('schulmanager',false);
        }).sendRequest(true);
    };
    /**
     * updates noten table in details view
     * @param key
     * @param result
     */
    SchulmanagerApp.prototype.updateDetailsNoten = function (key, result, id_prefix) {
        if (key == 'details_noten') {
            for (var block in result[key]) {
                for (var sIndex in result[key][block]) {
                    var index = parseInt(sIndex);
                    if (index >= -1 && index <= 11) {
                        for (var col in result[key][block][index]) {
                            var widget_col_id = id_prefix + key + '[' + block + ']' + '[' + index + ']' + '[' + col + ']';
                            var widget_col = document.getElementById(widget_col_id);
                            if (widget_col) {
                                widget_col.innerText = result[key][block][index][col];
                            }
                        }
                    }
                }
            }
        }
    };
    /**
     * selectes teacher for a new substitution changed, reload list
     */
    SchulmanagerApp.prototype.onDetailsNotenEdit = function (_action, _senders) {
        var idPostfix = _senders.id.substring(4);
        var isGlnw = _senders.id.substring(5, 9) == 'glnw';
        var modal = document.getElementById("schulmanager-notenmanager-notendetails_editcontentmodal");
        modal.style.display = "block";
        var widgetNote = document.getElementById('schulmanager-notenmanager-notendetails_details_noten' + idPostfix + '[note]');
        if (widgetNote) {
            var note_input = document.getElementById('schulmanager-notenmanager-notendetails_edit_note');
            note_input.value = widgetNote.innerText;
        }
        // select GLNW and KLNW
        var widgetTypeGKlnw = document.getElementById('schulmanager-notenmanager-notendetails_details_noten' + idPostfix + '[art]');
        if (widgetTypeGKlnw) {
            var selTypeGlnw = document.getElementById('schulmanager-notenmanager-notendetails_notgebart_glnw');
            var selTypeKlnw = document.getElementById('schulmanager-notenmanager-notendetails_notgebart_klnw');
            if (isGlnw) {
                for (var i = 0; i < selTypeGlnw.options.length; i++) {
                    if (selTypeGlnw.options[i].innerText == widgetTypeGKlnw.innerText) {
                        selTypeGlnw.options[i].selected = true;
                        break;
                    }
                }
                selTypeGlnw.style.display = "block";
                selTypeKlnw.options[0].selected = true;
                selTypeKlnw.style.display = "none";
                // update style
                jQuery(".details-grid-edit tr:nth-child(even)").css("background", "#fff9ba");
                jQuery(".details-grid-edit tr:nth-child(odd)").css("background", "#fff47c");
                jQuery(".details-grid-edit td").css("border", "1px solid #ffdd00");
                // end update style
            }
            else {
                for (var i = 0; i < selTypeKlnw.options.length; i++) {
                    if (selTypeKlnw.options[i].innerText == widgetTypeGKlnw.innerText) {
                        selTypeKlnw.options[i].selected = true;
                        break;
                    }
                }
                selTypeGlnw.options[0].selected = true;
                selTypeGlnw.style.display = "none";
                selTypeKlnw.style.display = "block";
                // update style
                jQuery(".details-grid-edit tr:nth-child(even)").css("background", "#beeaac");
                jQuery(".details-grid-edit tr:nth-child(odd)").css("background", "#a1e684");
                jQuery(".details-grid-edit td").css("border", "1px solid #7ecc67");
                // end update style
            }
            document.getElementById('schulmanager-notenmanager-notendetails_edit_type_flag').innerText = isGlnw ? "glnw" : "klnw";
        }
        // definition date
        var widgetDefDate = document.getElementById('schulmanager-notenmanager-notendetails_details_noten' + idPostfix + '[definition_date]');
        if (widgetDefDate) {
            var defDate_input = document.getElementById('schulmanager-notenmanager-notendetails_edit_date').firstChild;
            defDate_input.value = widgetDefDate.innerText;
        }
        // description
        var widgetDesc = document.getElementById('schulmanager-notenmanager-notendetails_details_noten' + idPostfix + '[description]');
        if (widgetDesc) {
            var desc_input = document.getElementById('schulmanager-notenmanager-notendetails_edit_desc');
            desc_input.value = widgetDesc.innerText;
        }
        // note kaay
        var note_key = document.getElementById('schulmanager-notenmanager-notendetails_edit_note_key');
        note_key.value = idPostfix;
    };
    /**
     * Commits edited noten value
     * @param _action
     * @param _senders
     */
    SchulmanagerApp.prototype.onDetailsNotenCommit = function (_action, _senders) {
        var egw = this.egw;
        var instance = this;
        this.egw.loading_prompt('schulmanager', true, egw.lang('please wait...'));
        // read data
        var noteElement = document.getElementById('schulmanager-notenmanager-notendetails_edit_note');
        var noteVal = noteElement.value;
        var typeFlagElement = document.getElementById('schulmanager-notenmanager-notendetails_edit_type_flag');
        var typeFlag = typeFlagElement.innerText;
        var note_type = 0;
        if (typeFlag == "glnw") {
            var selTypeGlnw = document.getElementById('schulmanager-notenmanager-notendetails_notgebart_glnw');
            note_type = selTypeGlnw.selectedIndex;
        }
        else if (typeFlag == "klnw") {
            var selTypeKlnw = document.getElementById('schulmanager-notenmanager-notendetails_notgebart_klnw');
            note_type = selTypeKlnw.selectedIndex;
        }
        var dateElement = document.getElementById('schulmanager-notenmanager-notendetails_edit_date').firstChild;
        var note_date = dateElement.value;
        var descElement = document.getElementById('schulmanager-notenmanager-notendetails_edit_desc');
        var note_desc = descElement.value;
        var tokenElement = document.getElementById('schulmanager-notenmanager-notendetails_token');
        var token = tokenElement.value;
        var noteKeyElement = document.getElementById('schulmanager-notenmanager-notendetails_edit_note_key');
        var noteKey = noteKeyElement.value;
        // send data
        var func = 'schulmanager.schulmanager_ui.ajax_noteDetailsModified';
        this.egw.json(func, [noteKey, noteVal, token, note_date, note_type, note_desc, typeFlag], function (result) {
            //alert("done");
            for (var key in result) {
                if (key == 'details_noten') {
                    instance.updateDetailsNoten(key, result, 'schulmanager-notenmanager-notendetails_');
                }
                else {
                    var widgetItem = document.getElementById('schulmanager-notenmanager-notendetails_' + key);
                    if (widgetItem) {
                        widgetItem.innerText = result[key];
                    }
                    else {
                        //alert(key);
                    }
                }
            }
            egw.loading_prompt('schulmanager', false);
        }).sendRequest(true);
        // hide div
        var modal = document.getElementById("schulmanager-notenmanager-notendetails_editcontentmodal");
        modal.style.display = "none";
    };
    SchulmanagerApp.prototype.onDetailsNotenCancel = function (_action, _senders) {
        var modal = document.getElementById("schulmanager-notenmanager-notendetails_editcontentmodal");
        modal.style.display = "none";
    };
    /**
     * Delete single
     * @param _action
     * @param _senders
     */
    SchulmanagerApp.prototype.onDetailsNotenDelete = function (_action, _senders) {
        var instance = this;
        var egw = this.egw;
        var tokenElement = document.getElementById('schulmanager-notenmanager-notendetails_token');
        var token = tokenElement.value;
        var noteKeyElement = document.getElementById('schulmanager-notenmanager-notendetails_edit_note_key');
        var noteKey = noteKeyElement.value;
        //var modal = document.getElementById("schulmanager-notenmanager-notendetails_editcontentmodal");
        //modal.style.display = "none";
        var action_id = _action.id;
        et2_widget_dialog_1.et2_dialog.show_dialog(function (button_id, value) {
            if (button_id != et2_widget_dialog_1.et2_dialog.NO_BUTTON) {
                var func = 'schulmanager.schulmanager_ui.ajax_noteDetailsDeleted';
                egw.json(func, [noteKey, token], function (result) {
                    for (var key in result) {
                        if (key == 'details_noten') {
                            instance.updateDetailsNoten(key, result, 'schulmanager-notenmanager-notendetails_');
                        }
                        else {
                            var widgetItem = document.getElementById('schulmanager-notenmanager-notendetails_' + key);
                            if (widgetItem) {
                                widgetItem.innerText = result[key];
                            }
                            else {
                            }
                        }
                    }
                }).sendRequest(true);
            }
        }, egw.lang('Confirmation required'), egw.lang('Confirmation required'), {}, et2_widget_dialog_1.et2_dialog.BUTTONS_OK_CANCEL, et2_widget_dialog_1.et2_dialog.QUESTION_MESSAGE);
        var modal = document.getElementById("schulmanager-notenmanager-notendetails_editcontentmodal");
        modal.style.display = "none";
    };
    /**
     * select students for a new class changed, reload list
     */
    SchulmanagerApp.prototype.onSchuelerViewKlasseChanged = function (_action, _senders) {
        if (_action === void 0) { _action = null; }
        if (_senders === void 0) { _senders = null; }
        var instance = this;
        var et2 = this.et2;
        var func = 'schulmanager.schulmanager_ui.ajax_schuelerViewKlasseChanged';
        var klasse_id = 0;
        if (_action !== null) {
            klasse_id = _action.value;
        }
        this.egw.json(func, [klasse_id], function (result) {
            /*
            var sla_nm = <et2_nextmatch>et2.getWidgetById('sla_nm');
            var sko_nm = <et2_nextmatch>et2.getWidgetById('sko_nm');
            sla_nm.applyFilters();
            sko_nm.applyFilters();
            */
            var not_nm = et2.getWidgetById('not_nm');
            not_nm.applyFilters();
            // ############### new version
            //instance.tableUpdate("schulmanager-schuelerview_grid-sla", result['sla_nm_rows']);
            //instance.tableUpdate("schulmanager-schuelerview_grid-sko", result['sko_nm_rows']);
            //instance.tableUpdate("schulmanager-schuelerview_grid-san", result['san_nm_rows']);
            // ##############################
            // update schueler select
            var widget = document.getElementById('schulmanager-schuelerview_select_schueler');
            if (widget) {
                var length_4 = widget.options.length;
                for (var i = length_4 - 1; i >= 0; i--) {
                    widget.options[i] = null;
                }
                for (var key in result['select_schueler']) {
                    var opt = document.createElement("option");
                    opt.value = key;
                    opt.text = result['select_schueler'][key];
                    widget.options.add(opt);
                }
            }
            delete (result['select_schueler']);
            instance.schuelerViewUpdate(instance, result);
            /*for (var key in result){
                if(key == 'details_noten'){
                }
                else {

                    var widgetItem = document.getElementById('schulmanager-schuelerview_' + key);
                    if (widgetItem) {
                        widgetItem.innerText = result[key];
                    }
                }
            }*/
            //egw.loading_prompt('schulmanager',false);
        }).sendRequest(true);
    };
    /**
     * select students for a new class changed, reload list
     */
    SchulmanagerApp.prototype.onSchuelerViewSchuelerChanged = function (_action, _senders) {
        var instance = this;
        //var egw = this.egw;
        //this.egw.loading_prompt('schulmanager',true,egw.lang('please wait...'));
        var et2 = this.et2;
        var func = 'schulmanager.schulmanager_ui.ajax_schuelerViewSchuelerChanged';
        var schueler_id = _action.value;
        this.egw.json(func, [schueler_id], function (result) {
            /*
            var sla_nm = <et2_nextmatch>et2.getWidgetById('sla_nm');
            var sko_nm = <et2_nextmatch>et2.getWidgetById('sko_nm');
            sla_nm.applyFilters('');
            sko_nm.applyFilters('');
            */
            var not_nm = et2.getWidgetById('not_nm');
            not_nm.applyFilters();
            instance.schuelerViewUpdate(instance, result);
            //egw.loading_prompt('schulmanager',false);
        }).sendRequest(true);
    };
    /**
     * updates nested content
     * @param instance
     * @param result
     */
    SchulmanagerApp.prototype.schuelerViewUpdate = function (instance, result) {
        // ############### new version
        instance.tableUpdate("schulmanager-schuelerview_grid-sla", result['sla_nm_rows']);
        instance.tableUpdate("schulmanager-schuelerview_grid-sko", result['sko_nm_rows']);
        instance.tableUpdate("schulmanager-schuelerview_grid-san", result['san_nm_rows']);
        //instance.tableUpdate("schulmanager-schuelerview_grid-noten", result['noten_nm_rows']);
        // ##############################
        for (var key in result) {
            if (key != 'sla_nm_rows' && key != 'sko_nm_rows' && key != 'san_nm_rows') {
                var widgetItem = document.getElementById('schulmanager-schuelerview_' + key);
                if (widgetItem) {
                    widgetItem.innerText = result[key];
                }
            }
        }
    };
    /**
     * appends a new row
     * @param id
     * @param row
     */
    SchulmanagerApp.prototype.tableUpdate = function (id, rows) {
        var table = document.getElementById(id);
        // delete rows
        while (table.rows.length > 1) {
            table.deleteRow(1);
        }
        // add new rows
        if (table) {
            for (var r = 0; r < rows.length; r++) {
                var newRow = table.insertRow(table.rows.length);
                var rowData = rows[r];
                for (var i = 0; i < rowData.length; i++) {
                    var cell = newRow.insertCell(i);
                    cell.innerHTML = rowData[i];
                }
            }
        }
    };
    /**
     * Serach accounts to map them to teachers
     */
    SchulmanagerApp.prototype.onTeacherAutoLinking = function (_action, _senders) {
        var et2 = this.et2;
        var func = 'schulmanager.schulmanager_substitution_ui.ajax_onTeacherAutoLinking';
        this.egw.json(func, [], function (result) {
            var nm = et2.getWidgetById('nm');
            nm.applyFilters();
        }).sendRequest(true);
    };
    /**
     * Before linking teacher tom egw account
     * @param _action
     * @param _senders
     */
    SchulmanagerApp.prototype.onTeacherAccountLinkEdit = function (_action, _senders) {
        var row_id = _senders[0]._index;
        var func = 'schulmanager.schulmanager_substitution_ui.ajax_onTeacherAccountLinkEdit';
        this.egw.json(func, [row_id], function (result) {
            var modal = document.getElementById("schulmanager-accounts_editcontentmodal");
            modal.style.display = "block";
            delete (result['link_account_id']);
            for (var key in result) {
                var widget_id = 'schulmanager-accounts_' + key;
                var widget = document.getElementById(widget_id);
                if (widget) {
                    widget.innerText = result[key];
                }
            }
        }).sendRequest(true);
    };
    /**
     * Commit teacher-account-link
     * @param _action
     * @param _senders
     */
    SchulmanagerApp.prototype.onTeacherAccountLinkCommit = function (_action, _senders) {
        var modal = document.getElementById("schulmanager-accounts_editcontentmodal");
        modal.style.display = "none";
        var et2 = this.et2;
        var selAccount = document.getElementById('schulmanager-accounts_account_id');
        var account = selAccount.value;
        var tokenDiv = document.getElementById('schulmanager-accounts_token');
        var token = tokenDiv.value;
        var func = 'schulmanager.schulmanager_substitution_ui.ajax_onTeacherAccountLinkCommit';
        this.egw.json(func, [account, token], function (result) {
            var nm = et2.getWidgetById('nm');
            nm.refresh(result['row_index'], et2_extension_nextmatch_1.et2_nextmatch.UPDATE);
        }).sendRequest(true);
    };
    /**
     * Reset single row, delete teacher-account-link
     * @param _action
     * @param _senders
     */
    SchulmanagerApp.prototype.onTeacherAccountLinkReset = function (_action, _senders) {
        var et2 = this.et2;
        var row_ids = [];
        for (var i = 0; i < _senders.length; i++) {
            row_ids.push(_senders[i]._index);
        }
        //var row_id = _senders[0]._index;
        var tokenDiv = document.getElementById('schulmanager-accounts_token');
        var token = tokenDiv.value;
        var func = 'schulmanager.schulmanager_substitution_ui.ajax_onTeacherAccountLinkReset';
        this.egw.json(func, [row_ids, token], function (result) {
            var nm = et2.getWidgetById('nm');
            //nm.refresh(result['row_id'], et2_nextmatch.UPDATE);
            nm.applyFilters();
        }).sendRequest(true);
    };
    SchulmanagerApp.prototype.delLnwPerA = function () {
        var et2 = this.et2;
        var egw = this.egw;
        var tokenDiv = document.getElementById('schulmanager-schuelerview_token');
        var token = tokenDiv.value;
        et2_widget_dialog_1.et2_dialog.show_dialog(function (button_id, value) {
            if (button_id == et2_widget_dialog_1.et2_dialog.OK_BUTTON) {
                egw.loading_prompt('schulmanager', true, egw.lang('please wait...'));
                var func = 'schulmanager.schulmanager_ui.ajax_delLnwPerA';
                egw.json(func, [token], function (result) {
                    var not_nm = et2.getWidgetById('not_nm');
                    not_nm.applyFilters();
                }).sendRequest(true);
                egw.loading_prompt('schulmanager', false);
            }
        }, egw.lang('Confirmation required'), egw.lang('Confirmation required'), {}, et2_widget_dialog_1.et2_dialog.BUTTONS_OK_CANCEL, et2_widget_dialog_1.et2_dialog.QUESTION_MESSAGE);
    };
    SchulmanagerApp.prototype.delLnwPerB = function () {
        var et2 = this.et2;
        var egw = this.egw;
        var tokenDiv = document.getElementById('schulmanager-schuelerview_token');
        var token = tokenDiv.value;
        et2_widget_dialog_1.et2_dialog.show_dialog(function (button_id, value) {
            if (button_id == et2_widget_dialog_1.et2_dialog.OK_BUTTON) {
                var func = 'schulmanager.schulmanager_ui.ajax_delLnwPerB';
                egw.json(func, [token], function (result) {
                }).sendRequest(true);
            }
            var not_nm = et2.getWidgetById('not_nm');
            not_nm.applyFilters();
        }, egw.lang('Confirmation required'), egw.lang('Confirmation required'), {}, et2_widget_dialog_1.et2_dialog.BUTTONS_OK_CANCEL, et2_widget_dialog_1.et2_dialog.QUESTION_MESSAGE);
        //var modal = document.getElementById("schulmanager-notenmanager-notendetails_editcontentmodal");
        //modal.style.display = "none";
    };
    SchulmanagerApp.prototype.resetAllGrades = function () {
        var et2 = this.et2;
        var tokenDiv = document.getElementById('schulmanager-mntc_token');
        var token = tokenDiv.value;
        et2_widget_dialog_1.et2_dialog.show_prompt(function (button_id, value) {
            if (button_id == et2_widget_dialog_1.et2_dialog.OK_BUTTON) {
                var func = 'schulmanager.schulmanager_mntc_ui.ajax_resetAllGrades';
                egw.json(func, [token, value], function (result) {
                    if (result.error_msg) {
                        egw(window).message(result.error_msg, 'error');
                    }
                    egw(window).message(result.msg, 'success');
                }).sendRequest(true);
            }
        }, egw.lang('Confirmation required'), egw.lang('Absolut sicher, dass alle Noten gelöscht werden sollen?'), {}, et2_widget_dialog_1.et2_dialog.BUTTONS_OK_CANCEL, et2_widget_dialog_1.et2_dialog.QUESTION_MESSAGE);
    };
    return SchulmanagerApp;
}(egw_app_1.EgwApp));
exports.SchulmanagerApp = SchulmanagerApp;
app.classes.schulmanager = SchulmanagerApp;
//# sourceMappingURL=app.js.map