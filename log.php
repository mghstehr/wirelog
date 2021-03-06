<HTML>
<HEAD><TITLE>log temperatures</TITLE></HEAD>
<?php
   include_once("utils.inc");
   $log = $_POST["log"];
   $tokens=explode(";", $log);
   if (sizeof($tokens) > 1) {
      // 1- save in one file per day (one dir per year, per month)
      // Extract date/time
      $dt = $tokens[0];
      // Adjust time from log to current timezone
      // For instance if log is 16.02.12/23:51;1;2;3;4
      // Adjusted time for winter is 17.02.12/00:51 and so log is done in file "17"
      $dt = ajustTimestampToCurrentTimezone($dt);
      list($d, $t) = explode("/", $dt);
      list($day, $month, $year) = explode(".", $d);
      // create directory for year 
      if (!file_exists($year)) {
         mkdir($year);
      }
      // create directory for month 
      if (!file_exists($year."/".$month)) {
         mkdir($year."/".$month);
      }
      // filename in which data are collected 
      $filename = $year."/".$month."/".$day;
      $handle = fopen($filename, "a");
      if ($handle) {
         fputs($handle, $log);
         fputs($handle, "\n");
         fclose($handle);
      }
      // 2-Save also in main log file (all in same file)
      $handle = fopen("mainLog", "a");
      if ($handle) {
         fputs($handle, $log);
         fputs($handle, "\n");
         fclose($handle);
      }
   }
?>
</HTML>
