<?php
/*
* @version 0.2 (auto-set)
*/

  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='zmzones';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  
   global $name;
   $rec['NAME']=$name;
   if ($rec['NAME']=='') {
    $out['ERR_NAME']=1;
    $ok=0;
   }
  
   global $zone;
   $rec['ZONE']=$zone;
   if ($rec['ZONE']=='') {
    $out['ERR_ZONE']=1;
    $ok=0;
   }

   global $script_id;
   $rec['SCRIPT_ID']=(int)$script_id; 
  
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
  
  $out['SCRIPTS']=SQLSelect("SELECT * FROM scripts ORDER BY TITLE");
?>