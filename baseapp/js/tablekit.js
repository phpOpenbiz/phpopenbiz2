/*
*
* Copyright (c) 2007 Andrew Tetlaw & Millstream Web Software
* http://www.millstream.com.au/view/code/tablekit/
* Version: 1.2.1 2007-03-11
* 
* Permission is hereby granted, free of charge, to any person
* obtaining a copy of this software and associated documentation
* files (the "Software"), to deal in the Software without
* restriction, including without limitation the rights to use, copy,
* modify, merge, publish, distribute, sublicense, and/or sell copies
* of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be
* included in all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
* EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
* NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
* BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
* ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
* CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
* * 
*/

// Use the TableKit class constructure if you'd prefer to init your tables as JS objects
var TableKit = Class.create();

TableKit.prototype = {
	initialize : function(elm, options) {
		var table = $(elm);
		if(table.tagName !== "TABLE") {
			return;
		}
		TableKit.register(table,Object.extend(TableKit.options,options || {}));
		this.id = table.id;
		var op = TableKit.option('sortable resizable editable', this.id);
		if(op.sortable) {
			TableKit.Sortable.init(table);
		} 
		if(op.resizable) {
			TableKit.Resizable.init(table);
		}
		if(op.editable) {
			TableKit.Editable.init(table);
		}
	},
	sort : function(column, order) {
		TableKit.Sortable.sort(this.id, column, order);
	},
	resizeColumn : function(column, w) {
		TableKit.Resizable.resize(this.id, column, w);
	},
	editCell : function(row, column) {
		TableKit.Editable.editCell(this.id, row, column);
	}
};

Object.extend(TableKit, {
	getBodyRows : function(table) {
		table = $(table);
		var id = table.id;
		if(!TableKit.rows[id]) {
			TableKit.rows[id] = (table.tHead && table.tHead.rows.length > 0) ? $A(table.tBodies[0].rows) : $A(table.rows).without(table.rows[0]);
		}
		return TableKit.rows[id];
	},
	getHeaderCells : function(table, cell) {
		if(!table) { table = $(cell).up('table'); }
		var id = table.id;
		if(!TableKit.heads[id]) {
			TableKit.heads[id] = $A((table.tHead && table.tHead.rows.length > 0) ? table.tHead.rows[table.tHead.rows.length-1].cells : table.rows[0].cells);
		}
		return TableKit.heads[id];
	},
	getCellIndex : function(cell) {
		return $A(cell.parentNode.cells).indexOf(cell);
	},
	getRowIndex : function(row) {
		return $A(row.parentNode.rows).indexOf(row);
	},
	getCellText : function(cell, refresh) {
		if(!cell) { return ""; }
		TableKit.registerCell(cell);
		var data = TableKit.cells[cell.id];
		if(refresh || data.refresh || !data.textContent) {
			data.textContent = cell.textContent ? cell.textContent : cell.innerText;
			data.refresh = false;
		}
		return data.textContent;
	},
	register : function(table, options) {
		if(!table.id) {
			TableKit._tblcount += 1;
			table.id = "tablekit-table-" + TableKit._tblcount;
		}
		var id = table.id;
		TableKit.tables[id] = TableKit.tables[id] ? Object.extend(TableKit.tables[id], options || {}) : Object.extend({sortable:false,resizable:false,editable:false}, options || {});
	},
	registerCell : function(cell) {
		if(!cell.id) {
			TableKit._cellcount += 1;
			cell.id = "tablekit-cell-" + TableKit._cellcount;
		}
		if(!TableKit.cells[cell.id]) {
			TableKit.cells[cell.id] = {textContent : '', htmlContent : '', active : false};
		}
	},
	isSortable : function(table) {
		return TableKit.tables[table.id] ? TableKit.tables[table.id].sortable : false;
	},
	isResizable : function(table) {
		return TableKit.tables[table.id] ? TableKit.tables[table.id].resizable : false;
	},
	isEditable : function(table) {
		return TableKit.tables[table.id] ? TableKit.tables[table.id].editable : false;
	},
	setup : function(o) {
		Object.extend(TableKit.options, o || {} );
	},
	option : function(s, id, o1, o2) {
		o1 = o1 || TableKit.options;
		o2 = o2 || (id ? (TableKit.tables[id] ? TableKit.tables[id] : {}) : {});
		var key = id + s;
		if(!TableKit._opcache[key]){
			TableKit._opcache[key] = $A($w(s)).inject([],function(a,v){
				a.push(a[v] = o2[v] || o1[v]);
				return a;
			});
		}
		return TableKit._opcache[key];
	},
	e : function(event) {
		return event || window.event;
	},
	tables : {},
	_opcache : {},
	cells : {},
	rows : {},
	heads : {},
	options : {
		autoLoad : true,
		stripe : true,
		sortable : true,
		resizable : true,
		editable : true,
		rowEvenClass : 'roweven',
		rowOddClass : 'rowodd',
		sortableSelector : ['table.sortable'],
		columnClass : 'sortcol',
		descendingClass : 'sortdesc',
		ascendingClass : 'sortasc',
		noSortClass : 'nosort',
		sortFirstAscendingClass : 'sortfirstasc',
		sortFirstDecendingClass : 'sortfirstdesc',
		resizableSelector : ['table.resizable'],
		minWidth : 10,
		showHandle : true,
		resizeOnHandleClass : 'resize-handle-active',
		editableSelector : ['table.editable'],
		formClassName : 'editable-cell-form',
		noEditClass : 'noedit',
		editAjaxURI : '/',
		editAjaxOptions : {}
	},
	_tblcount : 0,
	_cellcount : 0,
	load : function() {
		if(TableKit.options.autoLoad) {
			if(TableKit.options.sortable) {
				$A(TableKit.options.sortableSelector).each(function(s){
					$$(s).each(function(t) {
						TableKit.Sortable.init(t);
					});
				});
			}
			if(TableKit.options.resizable) {
				$A(TableKit.options.resizableSelector).each(function(s){
					$$(s).each(function(t) {
						TabLeKit.Resizable.init(t);
					});
				});
			}
			if(TableKit.options.editable) {
				$A(TableKit.options.editableSelector).each(function(s){
					$$(s).each(function(t) {
						TableKit.Editable.init(t);
					});
				});
			}
		}
	}
});



TableKit.Resizable = {
	init : function(elm, options){
		var table = $(elm);
		if(table.tagName !== "TABLE") {return;}
		TableKit.register(table,Object.extend(options || {},{resizable:true}));		 
		var cells = TableKit.getHeaderCells(table);
		cells.each(function(c){
			c = $(c);
			Event.observe(c, 'mouseover', TableKit.Resizable.initDetect);
			Event.observe(c, 'mouseout', TableKit.Resizable.killDetect);
		});
	},
	resize : function(table, index, w) {
		var cell;
		if(typeof index === 'number') {
			if(!table || (table.tagName && table.tagName !== "TABLE")) {return;}
			table = $(table);
			index = Math.min(table.rows[0].cells.length, index);
			index = Math.max(1, index);
			index -= 1;
			cell = (table.tHead && table.tHead.rows.length > 0) ? $(table.tHead.rows[table.tHead.rows.length-1].cells[index]) : $(table.rows[0].cells[index]);
		} else {
			cell = $(index);
			table = table ? $(table) : cell.up('table');
			index = TableKit.getCellIndex(cell);
		}
		var pad = parseInt(cell.getStyle('paddingLeft'),10) + parseInt(cell.getStyle('paddingRight'),10);
		w = Math.max(w-pad, TableKit.option('minWidth', table.id)[0]);
		
		cell.setStyle({'width' : w + 'px'});
	},
	initDetect : function(e) {
		e = TableKit.e(e);
		var cell = Event.element(e);
		Event.observe(cell, 'mousemove', TableKit.Resizable.detectHandle);
		Event.observe(cell, 'mousedown', TableKit.Resizable.startResize);
	},
	detectHandle : function(e) {
		e = TableKit.e(e);
		var cell = Event.element(e);
  		if(TableKit.Resizable.pointerPos(cell,Event.pointerX(e),Event.pointerY(e))){
  			cell.addClassName(TableKit.option('resizeOnHandleClass', cell.up('table').id)[0]);
  			TableKit.Resizable._onHandle = true;
  		} else {
  			cell.removeClassName(TableKit.option('resizeOnHandleClass', cell.up('table').id)[0]);
  			TableKit.Resizable._onHandle = false;
  		}
	},
	killDetect : function(e) {
		e = TableKit.e(e);
		TableKit.Resizable._onHandle = false;
		var cell = Event.element(e);
		Event.stopObserving(cell, 'mousemove', TableKit.Resizable.detectHandle);
		Event.stopObserving(cell, 'mousedown', TableKit.Resizable.startResize);
		cell.removeClassName(TableKit.option('resizeOnHandleClass', cell.up('table').id)[0]);
	},
	startResize : function(e) {
		e = TableKit.e(e);
		if(!TableKit.Resizable._onHandle) {return;}
		var cell = Event.element(e);
		Event.stopObserving(cell, 'mousemove', TableKit.Resizable.detectHandle);
		Event.stopObserving(cell, 'mousedown', TableKit.Resizable.startResize);
		Event.stopObserving(cell, 'mouseout', TableKit.Resizable.killDetect);
		TableKit.Resizable._cell = cell;
		var table = cell.up('table');
		TableKit.Resizable._tbl = table;
		if(TableKit.option('showHandle', table.id)[0]) {
			TableKit.Resizable._handle = $(document.createElement('div')).addClassName('resize-handle').setStyle({
				'top' : Position.cumulativeOffset(cell)[1] + 'px',
				'left' : Event.pointerX(e) + 'px',
				'height' : table.getDimensions().height + 'px'
			});
			document.body.appendChild(TableKit.Resizable._handle);
		}
		Event.observe(document, 'mousemove', TableKit.Resizable.drag);
		Event.observe(document, 'mouseup', TableKit.Resizable.endResize);
		Event.stop(e);
	},
	endResize : function(e) {
		e = TableKit.e(e);
		var cell = TableKit.Resizable._cell;
		TableKit.Resizable.resize(null, cell, (Event.pointerX(e) - Position.cumulativeOffset(cell)[0]));
		Event.stopObserving(document, 'mousemove', TableKit.Resizable.drag);
		Event.stopObserving(document, 'mouseup', TableKit.Resizable.endResize);
		if(TableKit.option('showHandle', TableKit.Resizable._tbl.id)[0]) {
			$$('div.resize-handle').each(function(elm){
				document.body.removeChild(elm);
			});
		}
		Event.observe(cell, 'mouseout', TableKit.Resizable.killDetect);
		TableKit.Resizable._tbl = TableKit.Resizable._handle = TableKit.Resizable._cell = null;
		Event.stop(e);
	},
	drag : function(e) {
		e = TableKit.e(e);
		if(TableKit.Resizable._handle === null) {
			try {
				TableKit.Resizable.resize(TableKit.Resizable._tbl, TableKit.Resizable._cell, (Event.pointerX(e) - Position.cumulativeOffset(TableKit.Resizable._cell)[0]));
			} catch(e) {}
		} else {
			TableKit.Resizable._handle.setStyle({'left' : Event.pointerX(e) + 'px'});
		}
		return false;
	},
	pointerPos : function(element, x, y) {
    	var offset = Position.cumulativeOffset(element);
	    return (y >= offset[1] &&
	            y <  offset[1] + element.offsetHeight &&
	            x >= offset[0] + element.offsetWidth - 5 &&
	            x <  offset[0] + element.offsetWidth);
  	},
	_onHandle : false,
	_cell : null,
	_tbl : null,
	_handle : null
};
