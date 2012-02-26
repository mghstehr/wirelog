<?php
   include_once("logFileParser.inc");
   include_once("settings.inc");

   // Make a table with all measurement from today
   function answerToday($reqId, $lines) {
      $nbOfLines=sizeof($lines);
      $nbOfMeasurements=sizeof($lines[0]);

      // Encode response as JSON 
      $response = "{version:'0.6'";
      // set reqId in response if it is present in request
      if ($reqId != "") {
         $response = $response.",reqId:'".$reqId."'";
      }
      // as hash value, we return the number of measurements
      // The client will use it when asking live data. It can be used
      // to detect that new data are available
      $response = $response.",sig:'$nbOfMeasurements'";
      // Add columns
      $response = $response.",status:'ok',table:{cols:[{label:'time', type:'datetime'}";
      for($i=1; $i<$nbOfLines; $i++) {
         $response = $response.",{label:'$sensors[$i]', type:'number'}";
      }
      // end columns array
      $response = $response."]";
      // Add rows
      $response = $response.",rows:[";
      for($i=0; $i<$nbOfMeasurements; $i++) {
         $value = $lines[0][$i];
         if ($i>0) {
            $response = $response.",";
         }
         $response = $response."{c:[{v:new Date($value)}"; // datetime
         for($j=1; $j<sizeof($lines); $j++) {
            $value = $lines[$j][$i];
            $response = $response.",{v:$value}";
         }
         $response = $response."]}";
      }
      $response = $response."]"; // end of rows
      $response = $response."}"; // end of table
      $response = $response."}"; // end of response

      return $response;
   }

   function answerTableHasNotChanged($reqId, $nbOfMeasurements) {

      // Encode response as JSON 
      $response = "{version:'0.6'";
      // set reqId in response if it is present in request
      if ($reqId != "") {
         $response = $response.",reqId:'".$reqId."'";
      }
      // as hash value, we return the number of lines
      // The client will use it when asking live data. It can be used
      // to detect that new data are available
      $response = $response.",sig:'$nbOfMeasurements'";
      // Add columns
      $response = $response.",status:'warning', warnings:[{reason:'other'}]}";
      //$response = $response.",status:'error'}";

      return $response;
   }

   // Debug 
   //$fd = fopen("testLog", "a");
   //fputs($fd, "datasource req Received\n");
   //foreach($_GET as $key => $i){
   //  fputs($fd, "$key=$_GET[$key]\n");
   //}

   $select = "";
   $reqId = "";
   $sig = "";

   // Parse request parameters (reqId, seq)
   $tqx = $_GET["tqx"];
   $tokens = explode(";", $tqx);
   for($i=0; $i<sizeof($tokens); $i++) {
      $pos=strpos($tokens[$i], "reqId");
      if ($pos !== false) {
         $subTokens = explode(":", $tokens[$i]);
         if (sizeof($subTokens) > 1) {
            $reqId=$subTokens[1];
         }
      } else {
         $pos=strpos($tokens[$i], "sig");
         if ($pos !== false) {
            $subTokens = explode(":", $tokens[$i]);
            if (sizeof($subTokens) > 1) {
               $sig=$subTokens[1];
            }
         }
      }
   }

   // Parse query (which data ? today, this date, from...to ?)
   $tq = $_GET["tq"]; 
   if ($tq != "") {
      $tokens = explode(",", $tq);
      for($i=0; $i<sizeof($tokens); $i++) {
         $pos=strpos($tokens[$i], "select");
         if ($pos !== false) {
            $subTokens = explode(":", $tokens[$i]);
            if (sizeof($subTokens) > 1) {
               $select=$subTokens[1];
               break;
            }
         }
      }
   }

   // Debug
   //fputs($fd, "reqId = $reqId\n");
   //fputs($fd, "sig = $sig\n");
   //fputs($fd, "select = $select\n");
   //fclose($fd);

   // Get sensors values for 'today'
   if ($select == "today") {
      $day = date("d");
      $month = date("m");
      $year = date("y");
      $lines = generateXYForOneDay($day, $month, $year);

      $nbOfMeasurements=sizeof($lines[0]);
      if ($sig == $nbOfMeasurements) {
         // table has not changed
         $response = answerTableHasNotChanged($reqId, $nbOfMeasurements);
      } else {
         $response = answerToday($reqId, $lines);
      }
      echo $response;
   }
?>
