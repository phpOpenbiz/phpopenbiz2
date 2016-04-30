<form id='{$name}' name='{$name}' style="border-bottom:2px solid #ABC3D7;border-top:2px solid #4E8CCF;">
<h2 class="title">{$title}</h2>
<div  class="listform">
{assign var='upd_time' value=''}
{section name="i" loop=$table}
{if $smarty.section.i.index != 0}
  <div style="padding: 2 2 2 2; font-size:12px">
    {if $upd_time != $table[i].bctrl_time}
  	   <div style="border-bottom: 1px solid silver; padding: 2 2 2 2;">
  	   {$table[i].bctrl_time} changed by {$table[i].bctrl_author}
  	   </div>
  	   {assign var='upd_time' value=$table[i].bctrl_time}
    {/if}
    {if $table[i].bctrl_field=='comment'}
      <div style="padding: 2 2 2 2; margin-left:20px; background-color:#F6F6F6">
      {$table[i].bctrl_newvalue}
      </div>
    {else}
    <div style="padding-left: 20px">
      <div style="float:left; font-weight:bold;padding: 0 10 0 0">{$table[i].bctrl_field}: </div>
      {if $table[i].bctrl_oldvalue != ''}
      <div style="float:left; "> changed from </div>
      <div style="float:left; padding: 0 10 0 10"><font color='green'>{$table[i].bctrl_oldvalue}</font></div> 
      <div style="float:left; "> to </div>
      {else}
      <div style="float:left; "> Set to </div>
      {/if}
      <div style="float:left; padding: 0 10 0 10"><font color='blue'>{$table[i].bctrl_newvalue}</font></div>
    </div>
    <div style="clear: both"></div>
    {/if}
  </div>
{/if}
{/section}
{if $smarty.section.i.total <= 1}
  <div style="padding: 2 2 2 2; font-size:12px">
  No change history found
  </div>
{/if}
</div>
</form>
