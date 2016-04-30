<form id={$name} name={$name}>
<center>
<table width=100% border=0 cellspacing=5 cellpadding=1 bgcolor="white" style="border:1px solid gray; font-size:12px">
<tr><td colspan=10 class="title">{$title}</td></tr>
<tr bgcolor="black"><td colspan=10><img src="../images/blank.gif" height="2"></td></tr>
{assign var="evt" value=""}
{assign var="i" value="0"}
{foreach from=$table item=row}
{if $i == 0 } <!-- skip the first line, the column header -->
   {assign var="i" value="1"}
   {assign var="col_name" value=$row.attdname}
   {assign var="col_phone" value=$row.attdphone}
   {assign var="col_email" value=$row.attdemail}
   {assign var="col_addr" value=$row.attdaddr}
   {assign var="col_fee" value=$row.fee}
{else}
   {if $row.evtname != $evt}
   <tr><td colspan=10><b>{$row.evtname}</b></td></tr>
   <tr>
   <td style="padding-left:20px"><b>{$col_name}</b></td>
   <td><b>{$col_phone}</b></td>
   <td><b>{$col_email}</b></td>
   <td><b>{$col_addr}</b></td>
   <td><b>{$col_fee}</b></td>
   </tr>
   <!--<tr bgcolor="silver"><td colspan=10><img src="../images/blank.gif"></td></tr>-->
   {/if}
   {assign var="evt" value=$row.evtname}
   <tr>
   <td style="padding-left:20px">{$row.attdname}</td>
   <td>{$row.attdphone}</td>
   <td>{$row.attdemail}</td>
   <td>{$row.attdaddr}</td>
   <td>{$row.fee}</td>
   </tr>
{/if}
{/foreach}
</table>
</center>
</form>
