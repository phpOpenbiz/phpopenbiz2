// --------- myjbForm ----------- //

function myjbForm(name) 
{
   myjbForm.prototype.jbForm = jbForm;
   this.jbForm(name);
   
   this.edit3_color = "blue";
}
//set inheritance
myjbForm.inheritsFrom(jbForm);

myjbForm.prototype.SelectRecord = function (params_array)
{
   alert('ok');
   // call parent method
   jbForm.prototype.SelectRecord.call(this, params_array);
}

//set methods
myjbForm.prototype.js_onMouseOverCombo1 = function () 
{
   var combo1 = this.GetFormControl("combo1");
   var edit3 = this.GetFormControl("edit3");
   edit3.value = combo1.value + " selected";
   if (this.edit3_color == "blue")
      this.edit3_color = "red";
   else
      this.edit3_color = "blue";
   edit3.style.color = this.edit3_color;
}
