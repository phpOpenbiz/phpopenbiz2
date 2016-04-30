<?php
include_once("CalHelper.inc");
// edit event
// new event
// delete event
class FMCalAttendee extends BizForm 
{
   protected $bShowSchedule = false;
   
   public function ShowSchedule()
   {
   	//$this->bShowSchedule = true;
   	$this->SetDisplayMode('SCHEDULE');
   	return $this->ReRender();
   }
   
   public function ShowAttendees()
   {
   	//$this->bShowSchedule = false;
   	$this->SetDisplayMode('READ');
   	return $this->ReRender();
   }
   
   public function RenderHTML()
   {	
      if ($this->m_Mode == 'SCHEDULE')
         return $this->RenderSchedule();
      else
         return parent::RenderHTML();
   }

   protected function RenderSchedule()
   {
      // get start, end time from the parent formobj 
      global $g_BizSystem;
      if (!$this->m_ParentFormName)
         return;
      $prtForm = $g_BizSystem->GetObjectFactory()->GetObject($this->m_ParentFormName);
      if (!$prtForm)
         return;
      $recArr = $prtForm->m_ActiveRecord;
      
      // timebound is the whole day of the current event date
      $d = getdate(strtotime($recArr['Start']));
      $st_tm = mktime(0, 0, 0, $d["mon"], $d["mday"], $d["year"]);
      $st = date("Y-m-d H:i:s", $st_tm);
      $et_tm = mktime(23, 59, 59, $d["mon"], $d["mday"], $d["year"]);
      $et = date("Y-m-d H:i:s", $et_tm);
            
      // get the attendee id list
      $recList = array();
      $this->GetDataObj()->FetchRecords($this->m_FixSearchRule, $recList);
      $idList = null;
      foreach($recList as $rec) {
         if ($idList == null)
            $idList .= "'".$rec['Id']."'";
         else
            $idList .= ","."'".$rec['Id']."'";
         $id_arr[] = $rec['Id'];
      }
      $idList = "(".$idList.")";
      
      // query events (BOCalendar) with the day against the attendee list
      $searchRule = "(([Repeated]<>'Y' AND [Start]>='$st' AND [Start]<'$et')";
      $searchRule .= " OR ([Repeated]='Y' AND [RepeatEnd]>'$st'))";
      $searchRule .= " AND [AttdId] IN $idList";
      $qry_events = array();
   	$prtForm ->GetDataObj()->FetchRecords($searchRule, $qry_events);
   	$events = BuildEventList($qry_events, $st, $et);
   	
   	$dispmode = $this->GetDisplayMode();
	   $this->SetDisplayMode($dispmode->GetMode());
   	$smarty = BizSystem::GetSmartyTemplate();
      
      $smarty->assign_by_ref("name", $this->m_Name);
      $smarty->assign_by_ref("title", $this->m_Title);
      
      //$tbar['btn_attdlist'] = $this->m_ToolBar->get('btn_attdlist')->Render();
      //$smarty->assign_by_ref("toolbar", $tbar);
      $smarty->assign_by_ref("toolbar", $this->m_ToolBar->Render());
      
      // draw the chart
   	$halfhrList = array();
   	$sHtml = "<table class='calScheduleTable' border=0 width=100% cellspacing=1 cellpadding=1>";
   	$day_st = 7; $day_et = 18; 
   	$sHtml .= "<tr><th>Name</th>";
	   for ($i=$day_st*2; $i<$day_et*2; $i++)
	   {
	      if ($i % 2 == 0)
	         $sHtml .= "<th colspan=2>".($i/2).":00</th>";
	   }
	   $sHtml .= "</tr>";
   	foreach($id_arr as $evt_id)
   	{
   	   $sHtml .= "<tr>";
   	   for ($i=0; $i<24*2; $i++)
   	      $halfhrList[$i] = 0;
   	   foreach($events as $evt)
   	   {
   	      if ($evt['AttdId'] == $evt_id) {
   	         $atd_fname = $evt["AttdFName"];
   	         $atd_lname = $evt["AttdLName"];
   	         $t0 = getEventStartTime($evt);
      	      $t1 = getEventEndTime($evt);
   	         $diffHalfHr0 = floor(($t0 - $st_tm)/(3600/2));
   	         $diffHalfHr1 = ceil(($t1 - $st_tm)/(3600/2));
   	         for ($i=$diffHalfHr0; $i<$diffHalfHr1; $i++)
   	            $halfhrList[$i] += 1;
   	      }
   	   }
   	   
   	   $sHtml .= "<td width=100 class='calScheduleName'>".$atd_fname." ".$atd_lname."</td>";
   	   
         $evt_start = floor((strtotime($recArr['Start']) - $st_tm)/(3600/2));
      	$evt_end = ceil((strtotime($recArr['End']) - $st_tm)/(3600/2))-1;
   	   
   	   //print_r($halfhrList); echo "<br>";
   	   for ($i=$day_st*2; $i<$day_et*2; $i++)
   	   {
   	      //if ($i == $evt_start) $css_cls = "calBusy_start";
   	      //else if ($i == $evt_end) $css_cls = "calBusy_end";
   	      if ($halfhrList[$i] == 1) {
   	        if ($i == $evt_start) $css_cls = "calBusy_start";
   	        else if ($i == $evt_end) $css_cls = "calBusy_end";
   	        else $css_cls = "calBusy";
   	      }
   	      else if ($halfhrList[$i] > 1) {
   	        if ($i == $evt_start) $css_cls = "calOverlap_start";
   	        else if ($i == $evt_end) $css_cls = "calOverlap_end";
   	        else $css_cls = "calOverlap";
   	      }
   	      else $css_cls = "calNoBusy";
   	      $sHtml .= "<td class='".$css_cls."'>&nbsp;</td>";
   	   }
   	   $sHtml .= "</tr>";
   	}

   	$sHtml .= "</table>";
      
      $smarty->assign_by_ref("fmttable", $sHtml);
      return $smarty->fetch($dispmode->m_TemplateFile);
   }
}