<?php
chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); 
  
include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$ctl = new control_modules();

include_once(DIR_MODULES . 'app_zoneminder/app_zoneminder.class.php');

$zoneminder = new app_zoneminder();
$zoneminder->getConfig();

// connecting to zone minder
$zmdb = new mysql($zoneminder->config['ZM_HOST'], $zoneminder->config['ZM_PORT'], $zoneminder->config['ZM_USERNAME'], $zoneminder->config['ZM_PASSWORD'], $zoneminder->config['ZM_BASE']); 
 
// Последний Event при запуске
$res = $zmdb->SelectOne("SELECT id FROM Events ORDER BY id DESC LIMIT 1");

$lastEvent = $res['id'];
echo "Last id = $lastEvent\r\n";

//запускаем цикл обработки событий
while(1){
   setGlobal((str_replace('.php', '', basename(__FILE__))).'Run', time(), 1);
   
   // Get events
   $res = $zmdb->SelectOne(
     "SELECT Events.id, Monitors.Name, Events.Notes ".
	 "FROM Events, Monitors ".
	 "WHERE (Events.MonitorId = Monitors.Id) AND (Events.id > $lastEvent) ".
	 "LIMIT 1");
	 
   if ($res != ""){
     echo "New event: ".$res['id']." Monitor:".$res['Name']." Notes:".$res['Notes']."r\n";
	 
     $list = SQLSelect('SELECT NAME, ZONE, SCRIPT_ID FROM zmzones');
	 foreach ($list as $itm){	   
	   echo "Array: ".$itm['NAME']." - ".$itm['ZONE']."r\n";
	   
	   if (($res['Name'] == $itm['NAME']) and strpos($res['Notes'], $itm['ZONE'])){
	     echo "Found ".$itm['NAME'].":".$itm['ZONE']."\r\n";
	     runScript($itm['SCRIPT_ID']);
         break;	     
	   }
	 }
	 
     $lastEvent = $res['id']; 
   }

   if (file_exists('./reboot') || $_GET['onetime']) 
   {           
     $zmdb->Disconnect();
     $db->Disconnect();
     exit;
   }
   
   sleep(1);
}

// closing database connection
$zmdb->Disconnect();
$db->Disconnect();

?>