<?php

function getEventStartTime(&$evtRec)
{
   $st = $evtRec['Start'];
   return strtotime($st);
}
function getEventEndTime(&$evtRec)
{
   $st = $evtRec['End'];
   return strtotime($st);
}

function &BuildEventList(&$qry_events, $st, $et)
{
   $events = array();
   
   $st_date = getdate(strtotime($st));
   
	// todo: put the following logic into calhelper.inc
	// Rebuild event list by expanding repeated events in the list
	foreach ($qry_events as $evt)
	{
	   if($evt['Repeated'] == 'Y') {
	      $mst_evt_st = getEventStartTime($evt);
	      $mst_evt_et = getEventEndTime($evt);
	      $mst_evt_re = strtotime($evt['RepeatEnd']);
	      $start_time = strtotime($st);
	      $end_time = strtotime($et);
	      $evt_date = getdate($mst_evt_st);
	      if ($evt['RepeatCycle'] == 'Every day') {
	         $num_days = 0;
            while(1) {
               $new_evt_st = $mst_evt_st + $num_days*24*3600;
               if ($new_evt_st > $end_time || $new_evt_st > $mst_evt_re) break;
               $new_evt_et = $mst_evt_et + $num_days*24*3600;
               $events[] = NewRepeatedEvent($evt, $new_evt_st, $new_evt_et);
               $num_days++;
            }
	      }
	      if ($evt['RepeatCycle'] == 'Every week') {
             $num_weeks = ceil(($start_time - $mst_evt_st)/(7*24*3600));
             if ($num_weeks>=0) {
               while(1) {
                  $new_evt_st = $mst_evt_st + $num_weeks*7*24*3600;
                  if ($new_evt_st > $end_time || $new_evt_st > $mst_evt_re) break;
                  $new_evt_et = $mst_evt_et + $num_weeks*7*24*3600;
                  $events[] = NewRepeatedEvent($evt, $new_evt_st, $new_evt_et);
                  $num_weeks++;
               }
            }
	      }
	      if ($evt['RepeatCycle'] == 'Every month') {
	         $st_date = getdate($start_time);
	         $month = $st_date['mon'];
	         $mst_date0 = getdate($mst_evt_st);
	         $mst_date1 = getdate($mst_evt_et);
	         while(1) {
	            $new_evt_st = mktime($mst_date0['hours'],$mst_date0['minutes'],$mst_date0['seconds'],$month,$mst_date0['mday'],$mst_date0['year']);
	            if ($new_evt_st > $end_time || $new_evt_st > $mst_evt_re) break;
	            $new_evt_et = mktime($mst_date1['hours'],$mst_date1['minutes'],$mst_date1['seconds'],$month,$mst_date1['mday'],$mst_date1['year']);
	            $events[] = NewRepeatedEvent($evt, $new_evt_st, $new_evt_et);
	            $month++;
	         }
         }
         if ($evt['RepeatCycle'] == 'Every year') {
	         $st_date = getdate($start_time);
	         $year = $st_date['year'];
	         $mst_date0 = getdate($mst_evt_st);
	         $mst_date1 = getdate($mst_evt_et);
	         while(1) {
	            $new_evt_st = mktime($mst_date0['hours'],$mst_date0['minutes'],$mst_date0['seconds'],$mst_date0['mon'],$mst_date0['mday'],$year);
	            if ($new_evt_st > $end_time || $new_evt_st > $mst_evt_re) break;
	            $new_evt_et = mktime($mst_date1['hours'],$mst_date1['minutes'],$mst_date1['seconds'],$mst_date1['mon'],$mst_date1['mday'],$year);
	            $events[] = NewRepeatedEvent($evt, $new_evt_st, $new_evt_et);
	            $year++;
	         }
         }
	   }
	   else
	      $events[] = $evt;
	}
	
	// todo: sort the event list
	return $events;
}

function NewRepeatedEvent(&$mst_evt, $new_evt_st, $new_evt_et)
{
   $new_evt = $mst_evt;
	$new_evt['Start'] = date("Y-m-d H:i:s", $new_evt_st);
   $new_evt['End'] = date("Y-m-d H:i:s", $new_evt_et);
   return $new_evt;
}
?>