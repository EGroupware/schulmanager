<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Combination of Klassengruppe ans Schuelerfach
 *
 * @author axel
 */
class schulmanager_note_bo {

	/**
	 * Instance of  so object
	 *
	 * @var schulmanager_so
	 */
	var $so;


	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->so = new schulmanager_note_so();
	}






	/**
	 * saves a note
	 *
	 * @param array $schulmanager_noge array with key => value of all needed datas
	 * @return string msg if somthing went wrong; nothing if all right
	 */
	function save($note)
	{
		$this->so->save($note);
	}



//put your code here
}
