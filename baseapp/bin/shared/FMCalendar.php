<?php
include_once("CalHelper.inc");

class FMCalendar extends BizForm 
{ 
    /* 
        The start day of the week. This is the day that appears in the first column
        of the calendar. Sunday = 0.
    */
    var $startDay = 0;

    /* 
        The start month of the year. This is the month that appears in the first slot
        of the calendar in the year view. January = 1.
    */
    var $startMonth = 1;

    /*
        The labels to display for the days of the week. The first entry in this array
        represents Sunday.
    */
    //var $dayNames = array("S", "M", "T", "W", "T", "F", "S");
    var $dayNames = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Sunday");
    
    /*
        The labels to display for the months of the year. The first entry in this array
        represents January.
    */
    var $monthNames = array("January", "February", "March", "April", "May", "June",
                            "July", "August", "September", "October", "November", "December");
                            
                            
    /*
        The number of days in each month. You're unlikely to want to change this...
        The first entry in this array represents January.
    */
    var $daysInMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    
    var $events = array();
    
    protected $m_Mode = "LIST"; // DAY|WEEK|MONTH|LIST

    protected $m_CalTime = 0;
   
   public function GetSessionVars($sessCtxt)
	{
      //$sessCtxt->GetObjVar($this->m_Name, "CalMode", $this->m_Mode);
      //$sessCtxt->GetObjVar($this->m_Name, "CalTime", $this->m_CalTime);
      $this->m_HistoryInfo = $sessCtxt->GetViewHistory($this->m_Name);
      if ($this->m_HistoryInfo) {
         $this->m_CalTime = $this->m_HistoryInfo[0] ? $this->m_HistoryInfo[0] : 0;
         $this->m_Mode = $this->m_HistoryInfo[1] ? $this->m_HistoryInfo[1] : "LIST";
      }
      if ($this->m_CalTime == 0)
         $this->m_CalTime = time();
	}
	
   public function SetSessionVars($sessCtxt)
	{
      //$sessCtxt->SetObjVar($this->m_Name, "CalMode", $this->m_Mode);
      //$sessCtxt->SetObjVar($this->m_Name, "CalTime", $this->m_CalTime);
      $sessCtxt->SetViewHistory($this->m_Name, $this->GetHistoryInfo());
	}
   
   public function GetHistoryInfo()
	{
	   if ($this->m_Mode == "LIST") // list mode doesn't have history
	     return null;
      $histInfo[0] = $this->m_CalTime;
      $histInfo[1] = $this->m_Mode;
      return $histInfo;
	}
   
   public function ShowToday()
   {
      $this->m_Mode = "DAY";
      $this->m_CalTime = time();
      return $this->ReRender();
   }
   
   public function ShowDay()
   {
      $this->m_Mode = "DAY";
      return $this->ReRender();
   }
   
   public function ShowWeek()
   {
      $this->m_Mode = "WEEK";
      return $this->ReRender();
   }
   
   public function ShowMonth()
   {
      $this->m_Mode = "MONTH";
      return $this->ReRender();
   }
   
   public function ShowList()
   {
      $this->m_Mode = "LIST";
      return $this->ReRender();
   }
   
   public function PrevPage()
   {
      if ($this->m_Mode == "LIST")
         return parent::MovePrev();
         
      if ($this->m_Mode == "DAY")
         $this->m_CalTime -= 24*3600;
      else if ($this->m_Mode == "WEEK")
         $this->m_CalTime -= 7*24*3600;
      else if ($this->m_Mode == "MONTH") {
         $d = getdate($this->m_CalTime);
         $a = $this->adjustDate($d["mon"]-1, $d["year"]);
         $this->m_CalTime = mktime(12, 0, 0, $a[0], 1, $a[1]);
      }
      return $this->ReRender();
   }
   
   public function NextPage()
   {
      if ($this->m_Mode == "LIST")
         return parent::MoveNext();
         
      if ($this->m_Mode == "DAY")
         $this->m_CalTime += 24*3600;
      else if ($this->m_Mode == "WEEK")
         $this->m_CalTime += 7*24*3600;
      else if ($this->m_Mode == "MONTH") {
         $d = getdate($this->m_CalTime);
         $a = $this->adjustDate($d["mon"]+1, $d["year"]);
         $this->m_CalTime = mktime(12, 0, 0, $a[0], 1, $a[1]);
      }
      return $this->ReRender();
   }
   
   protected function GetTimeBound()
   {
      $date = array();
      $month = array();
      $year = array();
      $d = getdate($this->m_CalTime);
      
      if ($this->m_Mode == "DAY") {
         $t0 = mktime(0, 0, 0, $d["mon"], $d["mday"], $d["year"]);
         $t1 = mktime(23, 59, 59, $d["mon"], $d["mday"], $d["year"]);
      }
      else if ($this->m_Mode == "WEEK"){
    	   $weekStart = $d["mday"] - $date["wday"];
    	   $weekEnd = $d["mday"] + 7 - $date["wday"];
         $t0 = mktime(0, 0, 0, $d["mon"], $weekStart, $d["year"]);
         $t1 = mktime(23, 59, 59, $d["mon"], $weekEnd, $d["year"]);
      }
      else if ($this->m_Mode == "MONTH") {
         $t0 = mktime(0, 0, 0, $d["mon"], 1, $d["year"]);
         $daysInMonth = $this->getDaysInMonth($month, $year);
         $t1 = mktime(23, 59, 59, $d["mon"]+1, $daysInMonth, $d["year"]);
      }
      $t[0] = date("Y-m-d H:i:s",$t0);
      $t[1] = date("Y-m-d H:i:s",$t1);
      return $t;
   }
   
   public function SetDisplayMode($mode)
   {
      // set display mode as READ
      parent::SetDisplayMode(MODE_R);
      $this->m_Mode = $mode;
   }

   public function Render()
   {
      if ($this->m_Mode == "LIST")
         return parent::Render();
      else {
         // don't runsearch, query is done in RenderHTML
         return $this->RenderHTML();
      }
   }
   
   public function RenderHTML()
   {
      if ($this->m_Mode == "LIST")
         return parent::RenderHTML();

      // generate event list by query the dataobj and expand the repeated events
      // query events between starttime and endtime
      $t = $this->GetTimeBound();
      $st = $t[0]; $et = $t[1];
      $searchRule = "([Repeated]<>'Y' AND [Start]>='$st' AND [Start]<'$et')";
      $searchRule .= " OR ([Repeated]='Y' AND [RepeatEnd]>'$st')";
      $qry_events = array();
   	$this->GetDataObj()->FetchRecords($searchRule, $qry_events);

   	$this->events = BuildEventList($qry_events, $st, $et);
      
      $dispmode = $this->GetDisplayMode();
	   $this->SetDisplayMode($dispmode->GetMode());
      $smarty = BizSystem::GetSmartyTemplate();
      
      $smarty->assign_by_ref("name", $this->m_Name);
      $smarty->assign_by_ref("title", $this->m_Title);
      $smarty->assign_by_ref("toolbar", $this->m_ToolBar->Render());
            
      // always enable nav buttons for calendar
      foreach ($this->m_NavBar as $ctrl)
         $nbar[$ctrl->m_Name] = $ctrl->Render();
      $smarty->assign_by_ref("navbar", $nbar);
      
      //$d = getdate(time());
      $d = getdate($this->m_CalTime);
      $header = $this->getCalendarHeader($d["mday"], $d["mon"], $d["year"]);
      if ($header)
         $smarty->assign_by_ref("header", $header);
         
      if ($this->m_Mode == "DAY")
         $smarty->assign_by_ref("block", $this->GetDayHTML($d["mday"], $d["mon"], $d["year"]));
      else if ($this->m_Mode == "WEEK")
         $smarty->assign_by_ref("block", $this->GetWeekHTML($d["mday"], $d["mon"], $d["year"]));
      else if ($this->m_Mode == "MONTH")
         $smarty->assign_by_ref("block", $this->GetMonthHTML($d["mon"], $d["year"]));
      else
         return;
      
	   return $smarty->fetch($dispmode->m_TemplateFile);
   }
   
   
    function getCalendarLink($month, $year)
    {
        return "";
    }
    
    /*
        Return the URL to link to  for a given date.
        You must override this method if you want to activate the date linking
        feature of the calendar.
        
        Note: If you return an empty string from this function, no navigation link will
        be displayed. This is the default behaviour.
    */
    function getDateLink($day, $month, $year)
    {
        return "";
    }
    
    /*
        Calculate the number of days in a month, taking into account leap years.
    */
    function getDaysInMonth($month, $year)
    {
        if ($month < 1 || $month > 12)
            return 0;
   
        $d = $this->daysInMonth[$month - 1];
   
        if ($month == 2) {
            // Check for leap year
            // Forget the 4000 rule, I doubt I'll be around then...
        
            if ($year%4 == 0) {
                if ($year%100 == 0)  {
                    if ($year%400 == 0)
                        $d = 29;
                }
                else
                    $d = 29;
            }
        }
    
        return $d;
    }
    
   function getCalendarHeader($d, $m, $y)
   {
      $date = getdate(mktime(12, 0, 0, $m, $d, $y));
    	$first = $date["wday"];
    	
      $monthName = $this->monthNames[$m - 1];
      $weekdayName = $this->dayNames[$first];
      $weekNum = (int) ($date['yday']/7);
      
      if ($this->m_Mode == "DAY")
         $header = "$monthName $d $weekdayName $y";
      else if ($this->m_Mode == "WEEK")
         $header = "Week $weekNum, " . $y;
      else if ($this->m_Mode == "MONTH")
         $header = "$monthName $y";
         
      return $header;
   }
/*   
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
*/
   function getDayHTML($d, $m, $y, $showYear = 1)
   {
      $date = getdate(mktime(12, 0, 0, $m, $d, $y));
    	$first = $date["wday"];
    	
      $monthName = $this->monthNames[$m - 1];
      $weekdayName = $this->dayNames[$first];
      
      // calculate the max overlaped events
      $max_overlap = 0;
      for ($i=0; $i<count($this->events); $i++) {
         $evt_i = &$this->events[$i];
         $overlap_i = 0;
         for ($j=0; $j<count($this->events); $j++) {
            if ($i==$j) continue;
            $evt_j = &$this->events[$j];
            $st_i = getEventStartTime($evt_i);  $et_i = getEventEndTime($evt_i);
            $st_j = getEventStartTime($evt_j);  $et_j = getEventEndTime($evt_j);
            if (($st_i >= $st_j && $st_i < $et_j) ||
                ($et_i > $st_j && $st_i <= $et_j))
               $overlap_i++;
         }
         if ($max_overlap < $overlap_i)
            $max_overlap = $overlap_i;
      }
      
      $numTblCol = $max_overlap+1;
      $colWidth = (int)(95/$numTblCol)."%";
      $DayStartTime = 8;
    	$DayEndTime = 17;
    	$DayHalfHours = (17-8)*2;
      for ($i = 0; $i < $DayHalfHours; $i++)  // 8:00 to 17:00
         $numColumns[$i] = $numTblCol;
    	
    	$header = "$monthName $d $weekdayName $y";
    	
    	$s .= "<table border=0 cellspacing=1 cellpadding=0 class=\"calTable\">\n";
    	
    	for ($i = 0; $i < $DayHalfHours; $i++) // 8:00 to 17:00
    	{
    	   $basetime = ($i%2==0) ? 0 : 30;
    	   $basetime_str = ($i%2==0) ? "00" : "30";
    	   $time = (int)($i/2)+$DayStartTime.":".$basetime_str;
    	   
       	$s .= "<tr >\n";
       	$s .= "<td align=\"center\" valign=\"top\" width='60' class='calTime'>$time</td>\n"; 
       	$t0 = mktime($DayStartTime+$i/2, $basetime, 0, $m, $d, $y);
    	   $t1 = mktime($DayStartTime+$i/2, $basetime+29, 0, $m, $d, $y);
    	   // find all events in the day
    	     $count = 0;
   	     foreach ($this->events as $evt) {
   	        $st = getEventStartTime($evt);
      	     $et = getEventEndTime($evt);
   	        if ($st >= $t0 && $st <= $t1) {
   	           $halfhours = ceil (($et - $st) / (30*60)); 
   	           $this->m_RecordRow->SetRecordArr($evt);
                 $controls = $this->m_RecordRow->Render(); 
   	           $evtTxt = date("g:ia",$st)."-".date("g:ia",$et)." ".$controls['evt_sub'];
   	           $half1 = "<td valign='top' width='$colWidth' rowspan='$halfhours' class='calDayEvtBk'>"; 
   	           $half1 .= "<div class='calEvttext'>$evtTxt</div></td>\n";
   	           $s .= $half1;
   	           $count++;
   	           for($k=0; $k<$halfhours; $k++)
   	             $numColumns[$i+$k]--;
   	        }
   	     }
   	   for ($k=0; $k<$numColumns[$i]; $k++)
   	     $s .= "<td valign='top' width='$colWidth' class='calTime'>&nbsp;</td>\n"; 
       	$s .= "</tr>\n";
      }
    	
    	$s .= "</table>\n";
    	
    	return $s; 
   }

   function getWeekHTML($d, $m, $y, $showYear = 1)
   {
    	$date = getdate(mktime(12, 0, 0, $m, $d, $y));
    	$first = $date["wday"];
    	
    	$weekStart = $d - $first;
    	$weekEnd = $d + 7 - $first;
    	
    	$monthName = $this->monthNames[$m - 1];
    	$weekNum = (int) ($date['yday']/7);
    	
    	$header = "Week $weekNum, " . $y;
    	
    	$s .= "<table border=0 cellspacing=1 cellpadding=0 class=\"calTable\">\n";
    	
    	$s .= "<tr class='calDayNamesRow'>\n";

    	    for ($i = 0; $i < 7; $i++)
    	    {
    	       $date = date("M d", mktime(12, 0, 0, $m, $weekStart+$i, $y));
    	        $s .= "<td WIDTH='14%'><div>";
    	        $s .= "$date ". $this->dayNames[($this->startDay+$i)%7] ;
      	     $s .= "</div></td>\n";       
        	    $d++;
    	    }
    	    $s .= "</tr>\n";
    	    
    	    $s .= "<tr>\n";
    	    for ($i = 0; $i < 7; $i++)
    	    {
    	        $t0 = mktime(0, 0, 0, $m, $weekStart+$i, $y);
    	        $t1 = mktime(0, 0, 0, $m, $weekStart+$i+1, $y);
    	        $s .= "<td valign='top' class='calWeekViewday'>\n";
    	        // find all events in the day
      	     foreach ($this->events as $evt) {
      	        $st = getEventStartTime($evt);
      	        $et = getEventEndTime($evt);
      	        if ($st > $t0 && $st < $t1) {
      	           $this->m_RecordRow->SetRecordArr($evt);
                    $controls = $this->m_RecordRow->Render(); 
      	           $evtTxt = date("g:ia",$st)."-".date("g:ia",$et)." ".$controls["evt_sub"];
      	           $s .= "<div class='calEvttext'>$evtTxt</div>";
      	        }
      	     }
      	     $s .= "</td>\n";       
        	    $d++;
    	    }
    	    $s .= "</tr>\n"; 
    	
    	$s .= "</table>\n";
    	
    	return $s;  	
   }

    /*
        Generate the HTML for a given month
    */
    function getMonthHTML($m, $y, $showYear = 1)
    {
        $s = "";
        
        $a = $this->adjustDate($m, $y);
        $month = $a[0];
        $year = $a[1];        
        
    	$daysInMonth = $this->getDaysInMonth($month, $year);
    	$date = getdate(mktime(12, 0, 0, $month, 1, $year));
    	
    	$first = $date["wday"];
    	$monthName = $this->monthNames[$month - 1];
    	
    	$prev = $this->adjustDate($month - 1, $year);
    	$next = $this->adjustDate($month + 1, $year);
    	
    	$header = $monthName . (($showYear > 0) ? " " . $year : "");
    	
    	$s .= "<table border=0 cellspacing=1 cellpadding=0 class=\"calTable\">\n";
    	
    	$s .= "<tr class='calDayNamesRow'>\n";
    	$s .= "<td WIDTH='14%'>" . $this->dayNames[($this->startDay)%7] . "</td>\n";
    	$s .= "<td WIDTH='14%'>" . $this->dayNames[($this->startDay+1)%7] . "</td>\n";
    	$s .= "<td WIDTH='14%'>" . $this->dayNames[($this->startDay+2)%7] . "</td>\n";
    	$s .= "<td WIDTH='14%'>" . $this->dayNames[($this->startDay+3)%7] . "</td>\n";
    	$s .= "<td WIDTH='14%'>" . $this->dayNames[($this->startDay+4)%7] . "</td>\n";
    	$s .= "<td WIDTH='14%'>" . $this->dayNames[($this->startDay+5)%7] . "</td>\n";
    	$s .= "<td WIDTH='14%'>" . $this->dayNames[($this->startDay+6)%7] . "</td>\n";
    	$s .= "</tr>\n";
    	
    	// We need to work out what date to start at so that the first appears in the correct column
    	$d = $this->startDay + 1 - $first;
    	while ($d > 1)
    	{
    	    $d -= 7;
    	}

        // Make sure we know when today is, so that we can use a different CSS style
        $today = getdate(time());
    	
    	while ($d <= $daysInMonth)
    	{
    	    $s .= "<tr>\n";       
    	    
    	    for ($i = 0; $i < 7; $i++)
    	    {
    	       $t0 = mktime(0, 0, 0, $month, $d, $year);
    	       $t1 = mktime(0, 0, 0, $month, $d+1, $year);
    	      
        	    //$class = ($year == $today["year"] && $month == $today["mon"] && $d == $today["mday"]) ? "calendarToday" : "calendar";
        	     if ($d <= 0 || $d > $daysInMonth) $class = "calOtherMonth";
        	     else if ($i == 6 || $i == 0) $class = "calWeekend";
        	     else $class = "calWeekday";
    	        $s .= "<td valign='top' class='$class'>";
    	        $s .= "<div class=calDaynum>";
    	        if ($d > 0 && $d <= $daysInMonth)
    	        {
    	            $link = $this->getDateLink($d, $month, $year);
    	            $s .= (($link == "") ? $d : "<a href=\"$link\">$d</a>");
    	        }
    	        else if ($d <= 0)
    	        {
       	        $prev = $this->adjustDate($m-1, $y);
                 $p_month = $prev[0];
                 $p_year = $prev[1];
             	  $p_daysInMonth = $this->getDaysInMonth($p_month, $p_year);
    	           $s .= $p_daysInMonth + $d;
    	        }
    	        else 
    	           $s .= $d - $daysInMonth;
      	     $s .= "</div>\n";
      	     
      	     // find all events in the day
      	     foreach ($this->events as $evt) {
      	        $st = getEventStartTime($evt);
      	        //echo "$st, $t0, $t1 # $month, $d, $year<br>";
      	        if ($st > $t0 && $st < $t1) {
      	           $this->m_RecordRow->SetRecordArr($evt);
                    $controls = $this->m_RecordRow->Render(); 
      	           $evtTxt = date("g:ia",$st)." ".$controls['evt_sub'];
      	           $s .= "<div class='calEvttext'>$evtTxt</div>";
      	        }
      	     }
      	     
      	     $s .= "</td>\n";
        	    $d++;
    	    }
    	    $s .= "</tr>\n";    
    	}
    	
    	$s .= "</table>\n";
    	
    	return $s;  	
    }

    /*
        Adjust dates to allow months > 12 and < 0. Just adjust the years appropriately.
        e.g. Month 14 of the year 2001 is actually month 2 of year 2002.
    */
    function adjustDate($month, $year)
    {
        $a = array();  
        $a[0] = $month;
        $a[1] = $year;
        
        while ($a[0] > 12)
        {
            $a[0] -= 12;
            $a[1]++;
        }
        
        while ($a[0] <= 0)
        {
            $a[0] += 12;
            $a[1]--;
        }
        
        return $a;
    }
}

?>