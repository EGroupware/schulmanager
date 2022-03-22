<?php
/**
 * EGroupware Schulmanager - merge documents
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;

/**
 * Contacts document merge
 */
class schulmanager_merge extends Api\Storage\Merge
{
    /**
     * Functions that can be called via menuaction
     *
     * @var array
     */
    var $public_functions = array(
        'download_by_request'	=> true,
        'show_replacements' 	=> true,
        "merge_entries"			=> true
    );

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();

        // overwrite global export-limit, if an addressbook one is set
        $this->export_limit = self::getExportLimit('schulmanager');

        // switch of handling of html formated content, if html is not used
        $this->parse_html_styles = Api\Storage\Customfields::use_html('schulmanager');
    }

    /**
     * Get schulmanager replacements
     *
     * @param int $id id of entry
     * @param string &$content=null content to create some replacements only if they are use
     * @param boolean $ignore_acl =false true: no acl check
     * @return array|boolean
     */
    protected function get_replacements($id,&$content=null,$ignore_acl=false)
    {
        if (!($replacements = $this->schulmanager_replacements($id,'',$ignore_acl, $content)))
        {
            return false;
        }
        if($content && strpos($content, '$$#') !== false)
        {
            $this->cf_link_to_expand($this->contacts->read($id, $ignore_acl), $content, $replacements,'addressbook');
        }

        // Links
        $replacements += $this->get_all_links('addressbook', $id, '', $content);
        if (!(strpos($content,'$$calendar/') === false))
        {
            $replacements += $this->schulmanager_replacements($id,!(strpos($content,'$$calendar/-1/') === false));
        }
        return $replacements;
    }

    /**
     * Return replacements for the schulmanager
     *
     * TODO
     * @param int $id id of entry
     * @param string $prefix='' prefix like eg. 'erole'
     * @return array|boolean
     */
    protected function schulmanager_replacements($id,$prefix='', &$content = null)
    {
        $rowsSchueler = Api\Cache::getSession('schulmanager', 'notenmanager_rows');
        $schueler = $rowsSchueler[$id - 1];

        $replacements = array();

        $replacements['$$schueler/name$$'] = $schueler['nm_st']['st_asv_familienname'];//'Max Mustermann';
        $replacements['$$schueler/rufname$$'] = $schueler['nm_st']['st_asv_rufname'];//'Max Mustermann';

        $schueleranschrift_bo = new schulmanager_schueleranschrift_bo();
        $schueleranschrift_bo->read($schueler['nm_st']['st_asv_id']);

        $hap = $schueleranschrift_bo->getHauptAnsprechPartner();
        $replacements['$$schueler/anschrift$$'] = $hap['san_asv_anschrifttext'];
        $replacements['$$schueler/anrede_hap$$'] = $hap['san_asv_anredetext'];
        $replacements['$$schueler/anschrift_strasse$$'] = $hap['san_asv_strasse'];
        $replacements['$$schueler/anschrift_nr$$'] = $hap['san_asv_nummer'];
        $replacements['$$schueler/anschrift_plz$$'] = $hap['san_asv_postleitzahl'];
        $replacements['$$schueler/anschrift_ort$$'] = $hap['san_asv_ortsbezeichnung'];
        $replacements['$$schueler/anschrift_ortsteil$$'] = $hap['san_asv_ortsteil'];
        $replacements['$$schueler/anschrift_staat$$'] = $hap['san_staat_anzeige'];

        $klassengr_schuelerfa = Api\Cache::getSession('schulmanager', 'actual_klassengr_schuelerfa');
        $replacements['$$klasse/name$$'] = $klassengr_schuelerfa->getKlasse_asv_klassenname();
        $replacements['$$klasse/fach$$'] = $klassengr_schuelerfa->getSchuelerfach_asv_anzeigeform();

        return $replacements;
    }

    /**
     * Get a list of placeholders provided.
     *
     * Placeholders are grouped logically.  Group key should have a user-friendly translation.
     */
    public function get_placeholder_list($prefix = '')
    {
        // Specific order for these ones
        $placeholders = [
            'schueler' => [
                [
                    'value' => $this->prefix($prefix, 'schueler/name', '{'),
                    'label' => lang('name')
                ],
                [
                    'value' => $this->prefix($prefix, 'schueler/rufname', '{'),
                    'label' => lang('Rufname')
                ],
                /*[
                    'value' => $this->prefix($prefix, 'schueler/adressfeld', '{'),
                    'label' => lang('Adressfeld mit Anschrift der Hauptansprechpartner')
                ],*/
                [
                    'value' => $this->prefix($prefix, 'schueler/anschrift', '{'),
                    'label' => lang('Anschrift der Hauptansprechpartner')
                ],
                [
                    'value' => $this->prefix($prefix, 'schueler/anrede_hap', '{'),
                    'label' => lang('Anrede der Hauptansprechpartner')
                ],
                [
                    'value' => $this->prefix($prefix, 'schueler/anschrift_strasse', '{'),
                    'label' => lang('Straße der Hauptansprechpartner')
                ],
                [
                    'value' => $this->prefix($prefix, 'schueler/anschrift_nr', '{'),
                    'label' => lang('Hausnummer der Hauptansprechpartner')
                ],
                [
                    'value' => $this->prefix($prefix, 'schueler/anschrift_plz', '{'),
                    'label' => lang('Postleitzahl der Hauptansprechpartner')
                ],
                [
                    'value' => $this->prefix($prefix, 'schueler/anschrift_ort', '{'),
                    'label' => lang('Ort der Hauptansprechpartner')
                ],
                [
                    'value' => $this->prefix($prefix, 'schueler/anschrift_ortsteil', '{'),
                    'label' => lang('Ortsteil der Hauptansprechpartner')
                ],
                [
                    'value' => $this->prefix($prefix, 'schueler/anschrift_staat', '{'),
                    'label' => lang('Staat der Hauptansprechpartner')
                ],
            ],
            'klasse' => [
                [
                    'value' => $this->prefix($prefix, 'klasse/name', '{'),
                    'label' => lang('name')
                ],
                [
                    'value' => $this->prefix($prefix, 'klasse/fach', '{'),
                    'label' => lang('fach')
                ],
            ],
        ];

        $this->add_customfield_placeholders($placeholders, $prefix);
        return $placeholders;
    }

    protected function add_schulmanager_placeholders(&$placeholders, $prefix)
    {
        Api\Translation::add_app('schulmanager');

        // NB: The -1 is actually ‑1, a non-breaking hyphen to avoid UI issues where we split on -
        $group = lang('Schulmanager fields:') . " # = 1, 2, ..., 20, ‑1";
        foreach(array(
                    'title'        => lang('Title'),
                    'description'  => lang('Description'),
                    'participants' => lang('Participants'),
                    'location'     => lang('Location'),
                ) as $name => $label)
        {
            $placeholders[$group][] = array(
                'value' => $this->prefix(($prefix ? $prefix . '/' : '') . 'calendar/#', $name, '{'),
                'label' => $label
            );
        }
    }

    /**
     * Get insert-in-document action with optional default document on top
     *
     * Overridden from parent to change the insert-in-email actions so we can
     * have a custom action handler.
     *
     * @param string $dirs Directory(s comma or space separated) to search
     * @param int $group see nextmatch_widget::egw_actions
     * @param string $caption ='Insert in document'
     * @param string $prefix ='document_'
     * @param string $default_doc ='' full path to default document to show on top with action == 'document'!
     * @param int|string $export_limit =null export-limit, default $GLOBALS['egw_info']['server']['export_limit']
     * @return array see nextmatch_widget::egw_actions
     */
    public static function document_action($dirs, $group=0, $caption='Insert in document', $prefix='document_', $default_doc='',
                                           $export_limit=null)
    {
        $actions = parent::document_action($dirs, $group, $caption, $prefix, $default_doc, $export_limit);

        // Change merge into email actions so we can customize them
        static::customise_mail_actions($actions);

        return $actions;
    }

    protected static function customise_mail_actions(&$action)
    {
        if(strpos($action['egw_open'], 'edit-mail') === 0)
        {
            unset($action['confirm_multiple']);
            $action['onExecute'] = 'javaScript:app.addressbook.merge_mail';
        }
        else if ($action['children'])
        {
            foreach($action['children'] as &$child)
            {
                static::customise_mail_actions($child);
            }
        }
    }
}
