<p>
<form id={$name} name={$name} enctype="multipart/form-data" method="post" action="controller.php?F=Invoke&P0=[{$name}]&P1=[Upload111]">
<b>{$title}</b><hr>
{if $formstate == 0}
   <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
   {$fields.txt_file.label}: {$fields.txt_file.control}
   <hr>
   {$toolbar.btn_submit}{$toolbar.btn_close}
{elseif $formstate == 1}
   File import is done.
   <hr>
   {$toolbar.btn_done}
{/if}
</form>
</p>