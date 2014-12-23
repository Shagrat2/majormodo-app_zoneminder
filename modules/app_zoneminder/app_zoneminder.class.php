<?php
/**
* ZoneMinder
*
* ZoneMinder
*
* @package MajorDoMo
* @author Ivan Zaicev <ivan@jad.ru> http://smartliving.ru/
* @version 0.1
*/
//
//

class app_zoneminder extends module {
/**
* zoneminder
*
* Module class constructor
*
* @access private
*/
function app_zoneminder() {
  $this->name="app_zoneminder";
  $this->title="ZoneMinder";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   //$this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['TAB']=$this->tab;
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
  $this->getConfig();
  $out['ZM_HOST']=$this->config['ZM_HOST'];
  $out['ZM_PORT']=$this->config['ZM_PORT'];
  $out['ZM_BASE']=$this->config['ZM_BASE'];
  $out['ZM_USERNAME']=$this->config['ZM_USERNAME'];
  $out['ZM_PASSWORD']=$this->config['ZM_PASSWORD'];
  
  if (!$out['ZM_HOST']) {
    $out['ZM_HOST']='localhost';
  }
  
  if (!$out['ZM_USERNAME']) {
    $out['ZM_USERNAME']='root';
  }
 
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='zonemonder' || $this->data_source=='') {  
  if ($this->view_mode=='' || $this->view_mode=='search_zones') {
   $this->search_zones($out);
  }
  if ($this->view_mode=='edit_zone') {
   $this->edit_zone($out, $this->id);
  }
  if ($this->view_mode=='delete_zone') {
   $this->delete_zone($this->id);
   $this->redirect("?");
  }
  if ($this->view_mode=='update_settings') {
   global $zm_host;
   global $zm_port;
   global $zm_base;
   global $zm_username;
   global $zm_password;   

   $this->config['ZM_HOST']=$zm_host;
   $this->config['ZM_PORT']=(int)$zm_port;   
   $this->config['ZM_BASE']=$zm_base;
   $this->config['ZM_USERNAME']=$zm_username;
   $this->config['ZM_PASSWORD']=$zm_password;
   
   $this->saveConfig();
   $this->redirect("?"); 
  }
 }
}
/**
* users search
*
* @access public
*/
 function search_zones(&$out) {
  require(DIR_MODULES.$this->name.'/zones_search.inc.php');
 }
/**
* users edit/add
*
* @access public
*/
 function edit_zone(&$out, $id) {
  require(DIR_MODULES.$this->name.'/zone_edit.inc.php');
 }
/**
* users delete record
*
* @access public
*/
 function delete_zone($id) {
  $rec=SQLSelectOne("SELECT * FROM zmzones WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM zmzones WHERE ID='".$rec['ID']."'");
 }

/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($parent_name="") {
  parent::install($parent_name);
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS zmzones');
  parent::uninstall();
 }
 /**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
zmzones - Zones
*/
  $data = <<<EOD
 zmzones: ID int(10) unsigned NOT NULL auto_increment
 zmzones: NAME varchar(255) NOT NULL DEFAULT ''
 zmzones: ZONE varchar(255) NOT NULL DEFAULT ''
 zmzones: SCRIPT_ID int(10) NOT NULL DEFAULT '0'
EOD;
  parent::dbInstall($data);
 }

}

?>