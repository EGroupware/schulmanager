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

import 'jquery';
import 'jqueryui';
import '../jsapi/egw_global';
import '../etemplate/et2_types';

import {EgwApp} from '../../api/js/jsapi/egw_app';
//import {et2_nextmatch} from "../../api/js/etemplate/et2_extension_nextmatch";
import {etemplate2} from "../../api/js/etemplate/etemplate2";
// Object.defineProperty(exports, "__esModule", { value: true });

export class SchulmanagerApp extends EgwApp
{
	readonly appname = 'schulmanager';

	constructor()
	{
		super('schulmanager');
	}

	/**
	 * This function is called when the etemplate2 object is loaded
	 * and ready.  If you must store a reference to the et2 object,
	 * make sure to clean it up in destroy().
	 *
	 * @param et2 etemplate2 Newly ready object
	 * @param string name
	 */
	et2_ready(et2, name: string)
	{
		// call parent
		super.et2_ready(et2, name);

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
	}


	/**
	 * laden der gewichtungen beim ersten Laden des Templates
	 * @param {type} _action
	 * @returns {undefined}
	 */
	header_change()
	{
		var et2 = this.et2;
		var func = 'schulmanager.notenmanager_ui.ajax_getGewichtungen';
		var query = '';
		this.egw.json(func, [query], function (totals) {
			for (var key in totals){
				var widget = et2.getWidgetById(key);
				if(widget){
					widget.set_value(totals[key]);
				}
			}
		}).sendRequest(true);
	}

	// export notenbuch
	exportpdf_nb(_id, _widget)
	{
		this.egw.loading_prompt('schulmanager',true,egw.lang('please wait...'));
	    var req = new XMLHttpRequest();
		req.open("POST", egw.link('/index.php','menuaction=schulmanager.schulmanager_download_ui.exportpdf_nb'), true);
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
				} else if (typeof window.navigator.msSaveBlob !== 'undefined') {
					// IE version
					var blob = new Blob([req.response], { type: 'application/pdf' });
					window.navigator.msSaveBlob(blob, filename);
				} else {
					// Firefox version
					var file = new File([req.response], filename, { type: 'application/force-download' });
					window.open(URL.createObjectURL(file));
				}
			}
			egw.loading_prompt('schulmanager',false);
		};
		req.send();
	}

	/**
	 * test download pdf
	 * @param {type} _id
	 * @param {type} _widget
	 * @returns {undefined}
	 */
	exportpdf_kv(_id, _widget){
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

		this.egw.loading_prompt('schulmanager',true,egw.lang('please wait...'));
	    var req = new XMLHttpRequest();
		req.open("POST", egw.link('/index.php','menuaction=schulmanager.schulmanager_download_ui.exportpdf_kv'), true);
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
				} else if (typeof window.navigator.msSaveBlob !== 'undefined') {
					// IE version
					var blob = new Blob([req.response], { type: 'application/pdf' });
					window.navigator.msSaveBlob(blob, filename);
				} else {
					// Firefox version
					var file = new File([req.response], filename, { type: 'application/force-download' });
					window.open(URL.createObjectURL(file));
				}
			}
			egw.loading_prompt('schulmanager',false);
		};
		req.send();
	}

        // export notenbuch
	exportpdf_calm(_id, _widget)
	{
		var egw = this.egw;
        egw.loading_prompt('schulmanager',true,egw.lang('please wait...'));
	    var req = new XMLHttpRequest();
		req.open("POST", egw.link('/index.php','menuaction=schulmanager.schulmanager_download_ui.exportpdf_calm'), true);
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
				} else if (typeof window.navigator.msSaveBlob !== 'undefined') {
					// IE version
					var blob = new Blob([req.response], { type: 'application/pdf' });
					window.navigator.msSaveBlob(blob, filename);
				} else {
					// Firefox version
					var file = new File([req.response], filename, { type: 'application/force-download' });
					window.open(URL.createObjectURL(file));
				}
			}
			egw.loading_prompt('schulmanager',false);
		};
		req.send();
	}


	/**
	 * laden der weekdays
	 * @param {type} _action
	 * @returns {undefined}
	 */
	cal_header_change()
	{		
		var et2 = this.et2;
		var func = 'schulmanager.schulmanager_cal_ui.ajax_getWeekdays';

		var query = '';
		this.egw.json(func, [query], function (result) {
			//var widget = et2.getWidgetById('glnw_1_0');
			//widget.set_value(totals['glnw_1_0']);
			for (var key in result['nm_header']){
				//alert(key);
				var widget = et2.getWidgetById(key);
				if(widget){
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
	}

	cal_hide_items()
	{
		egw.css(".sm_cal_hidden","display:none");
	}

	cal_focus_item(_action, widget)
	{
		//alert(widget.getDOMNode());
		var _send = function() {
			egw().json(
				'schulmanager.schulmanager_cal_ui.ajax_editCalEvent',
				[
					widget.id
				],
				// Remove loading spinner
				function(result) {					
					jQuery(_action).blur();
					//alert(result['test']);
				}
			).sendRequest(true);
		};
		_send();
	}

	calEditMultiple(_action, widget)
	{
		//alert(widget.id);
		var _send = function() {
			egw().json(
				'schulmanager.schulmanager_cal_ui.ajax_editCalEvent',
				[
					widget.id,
					true
				],
				// Remove loading spinner
				function(result) {
					jQuery(_action).blur();
					//alert(result['test']);
				}
			).sendRequest(true);
		};
		_send();
	}

	calDeleteEvent(_action, widget)
	{
		//alert(widget[0].id);
		var _send = function() {
			egw().json(
				'schulmanager.schulmanager_cal_ui.ajax_deleteEvent',
				[
					widget[0].id
				],
				// Remove loading spinner
				function(result) {
					egw(window).refresh(result['msg'], 'schulmanager', 'schulmanager-calendar-editevent', 'update');
					var nm = etemplate2.getById('schulmanager-calendar-editevent').widgetContainer.getWidgetById('nm');
					nm.refresh(null,'update');
				}
			).sendRequest(true);
		};
		_send();
	}

	add_list_event(widget,prefix){
		var et2 = this.et2;
		//alert(prefix.concat('sm_type_options_list'));
		var type_id = prefix.concat('sm_type_options_list');
		var fach_id = prefix.concat('sm_fach_options_list');
		var user_id = prefix.concat('sm_user_list');

		//alert(type_id);
		var type = (<HTMLInputElement> document.getElementById(type_id)).value;
		var fach = (<HTMLInputElement> document.getElementById(fach_id)).value;
		var user = null;

		if(document.getElementById(user_id)){
			user = (<HTMLInputElement> document.getElementById(user_id)).value;
		}
		else{
			user_id = prefix.concat('sm_activeuserID');
			user = (<HTMLInputElement> document.getElementById(user_id)).value;
		}

		//alert(type);
		//alert(fach);
		//alert(user);

		var _send = function() {
			egw().json(
				'schulmanager.schulmanager_cal_ui.ajax_addEventToList',
				[
					type,
					fach,
					user
				],
				// Remove loading spinner
				function(result : any) {
					egw(window).refresh(result['msg'], 'schulmanager', 'schulmanager-calendar-editevent', 'update');
					var nm = etemplate2.getById('schulmanager-calendar-editevent').widgetContainer.getWidgetById('nm');
					nm.refresh(null,'update');
				}
			).sendRequest(true);
		};
		_send();

	}

	calShowAddEvent(action, selected){
		//alert("test");
		jQuery('table.addnewevent').css('display','inline');
	}

	nmEditGew(_action, widget)
	{
		//alert("test");
		//alert(widget.id);
		jQuery('table.editgew').css('display','inline');
	//	jQuery('table.editgew-content').css('display','inline');
	}


	changeNote(action, _senders)
	{
		var et2 = this.et2;
		var func = 'schulmanager.notenmanager_ui.ajax_noteModified';
		var noteKey = action.name;
		var noteVal = action.value;
		if(action.type == "checkbox"){
			if(action.checked){
				noteVal = 1;
			}else{
				noteVal = 0;
			}
		}
		this.egw.json(func, [noteKey, noteVal], function (result) {
			jQuery(action).addClass('schulmanager_note_changed');
			for (var key in result){
				var cssAvgKey = 'schulmanager-notenmanager-edit_'+key+'[-1][note]';
				var widget = <HTMLInputElement> document.getElementById(cssAvgKey);
				if(widget){
					widget.value = result[key]['[-1][note]'];
					if(action.id == cssAvgKey){
						// reset css class if this input value has been modified
						jQuery(action).removeClass('nm_avg_manuell');
						jQuery(action).removeClass('nm_avg_auto');
						jQuery(action).addClass(result[key]['avgclass']);
					}
				}
			}
		}).sendRequest(true);
	}


	
    changeGew(action, senders)
	{
		var et2 = this.et2;
		var func = 'schulmanager.notenmanager_ui.ajax_gewModified';
		var gewKey = action.name;
		var gewVal = action.value;

		this.egw.json(func, [gewKey, gewVal], function (result) {
			jQuery(action).addClass('schulmanager_note_changed');
			for (var key in result){
				var cssAvgKey = 'schulmanager-notenmanager-edit_'+key+'[-1][note]';
				var widget = <HTMLInputElement> document.getElementById(cssAvgKey);
				if(widget){
					widget.value = result[key]['[-1][note]'];
				}
			}
		}).sendRequest(true);
	}

	changeGewAllModified(_action, _senders)
	{

		var et2 = this.et2;
		var func = 'schulmanager.notenmanager_ui.ajax_gewAllModified';
		var noteKey = _action.name;
		var noteVal = _action.value;
		if(_action.type == "checkbox"){
			if(_action.checked){
				noteVal = 1;
			}else{
				noteVal = 0;
			}
		}
		this.egw.json(func, [noteKey, noteVal], function (result) {
			jQuery(_action).addClass('schulmanager_note_changed');
			for (var key in result){
				var cssAvgKey = 'schulmanager-notenmanager-edit_'+key+'[-1][note]';
				var cssAltBKey = 'schulmanager-notenmanager-edit_'+key+'[-1][checked]';
				var widget = <HTMLInputElement> document.getElementById(cssAvgKey);
				var widgetAltB = <HTMLInputElement> document.getElementById(cssAltBKey);
				if(widget){
					widget.value = result[key]['[-1][note]'];
					if(_action.id == cssAvgKey){
						jQuery(_action).removeClass('nm_avg_manuell');
						jQuery(_action).removeClass('nm_avg_auto');
						jQuery(_action).addClass(result[key]['avgclass']);
					}
				}
				else if(widgetAltB){
					widgetAltB.checked = result[key]['[-1][checked]'];
				}
			}
		}).sendRequest(true);
	}
	
	/**
	 * selectes teacher for a new substitution changed, reload list
	 */
	subs_TeacherChanged(_action, _senders)
	{

		var et2 = this.et2;
		var func = 'schulmanager.schulmanager_substitution_ui.ajax_getTeacherLessonList';
		var teacher_id = _action.value;
		
		this.egw.json(func, [teacher_id], function (result) {		
			var widget = <HTMLSelectElement> document.getElementById('schulmanager-substitution_add_lesson_list');
					
			if(widget){
				var length = widget.options.length;
				for (var i = length-1; i >= 0; i--) {
					widget.options[i] = null;
				}
				for (var key in result){
					var opt = document.createElement("option");
					opt.value = key;
					opt.text = result[key];
					widget.options.add(opt);
				}
			}				
		}).sendRequest(true);
	}
}

app.classes.schulmanager = SchulmanagerApp;
