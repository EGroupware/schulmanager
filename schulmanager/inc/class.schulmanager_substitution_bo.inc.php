<?php

/**
 * SchulManager - User interface
 */
use EGroupware\Api;
use EGroupware\Api\Egw;
use EGroupware\Api\Link;
use EGroupware\Api\Framework;
use EGroupware\Api\Acl;
use EGroupware\Api\Etemplate;

/**
 * This class is the UI-layer (user interface)
 */
class schulmanager_substitution_bo
{
	
	/**
	 * instance of the bo-class
	 *
	 * @var schulmanager_bo
	 */
	var $so;

	/**
	 * Constructor
	 *
	 * @return notenmanager_ui
	 */
	function __construct(Etemplate $etemplate = null)
	{		
		$this->so = new schulmanager_substitution_so();
	}

	
	function getSubstitutionList(&$query_in,&$rows)
	{
	    return $this->so->get_rows($query_in['filter'], $rows);
	    
	}
	
	function delete($substitution){
	    if(is_array($substitution)){
	        return $this->so->delete($substitution['id']);
	    }
	    return false;
	}
	
	function add($asv_kennung_id, $asv_kennung_orig_id, $lesson_index){
	    $rows = Api\Cache::getSession('schulmanager', 'substitution_rows');
	    
	    $lesson_list = Api\Cache::getSession('schulmanager', 'substitution_lesson_list');
	    
	    if($lesson_index >= 0 && $lesson_index < count($lesson_list)){
	        $lesson = $lesson_list[$lesson_index];
	        
	        $teacher = Api\Accounts::read($asv_kennung_id);
	        $teacher_orig = Api\Accounts::read($asv_kennung_orig_id);
	        
	        $kg_asv_id = $lesson->getKlassengruppe_asv_id();
	        $kg_asv_kennung = $lesson->getKlassengruppe_asv_kennung();
	        
	        $kl_asv_id = $lesson->getKlasse_asv_id();
	        $kl_asv_klassenname = $lesson->getKlasse_asv_klassenname();
	        
	        $sf_asv_id = $lesson->getSchuelerfach_asv_id();
	        $sf_asv_kurzform = $lesson->getSchuelerfach_asv_kurzform();
	        $sf_asv_anzeigeform = $lesson->getSchuelerfach_asv_anzeigeform();
	        
	        
	        return $this->so->save($teacher['account_lid'], $teacher_orig['account_lid'], $kg_asv_id, $kg_asv_kennung, $kl_asv_id, $kl_asv_klassenname, $sf_asv_id, $sf_asv_kurzform, $sf_asv_anzeigeform);
	    }
	    
	    return false;
	        
	    //$asv_kennung, $asv_kennung_orig, $kg_asv_id, $kg_asv_kennung, $kl_asv_id, $kl_asv_klassenname, $sf_asv_id, $sf_asv_kurzform, $sf_asv_anzeigeform){
	}
}