

Class('App.QKZX.PublishNotifyDetails', 'xui.Module',{
	Instance:{
		autoDestroy : true,
		properties : {},
		initialize : function(){
		},
		iniComponents : function(){
			// [[Code created by CrossUI RAD Studio
			var host=this, children=[], append=function(child){children.push(child.get(0));};

			append(
				(new xui.UI.TreeGrid())
				.setHost(host,"grid")
				.setShowDirtyMark(false)
				.setSelMode("single")
				.setRowNumbered(true)
				.setRowHandlerWidth(26)
				.setTreeMode(false)
			);

			host.grid.append((new xui.UI.Image())
				.setHost(host, 'btn_del')
				.setLeft('auto')
				.setTop(2)
				.setHeight(16)
				.setWidth(16)
				.setSrc('@xui_ini.appPath@image/remove.png')
				.setDisplay('none')
				.setCursor('pointer')
				.setTips('删除此行')
				.onClick('_remove_clicked'));
			return children;
			// ]]Code created by CrossUI RAD Studio
		},
		events:{"onRender":"_com_onrender"},
		_com_onrender:function (com, threadid){
			var ns=this,grid=ns.grid;
			if(!ns.properties.setting.readonly){
				grid.setEditable(true);
				grid.onInitHotRow("_grid_oninithotrow");
				grid.beforeHotRowAdded("_grid_beforehotrowadded");
				grid.afterHotRowAdded("_grid_afterhotrowadded");
				grid.afterCellUpdated("_grid_aftercellupdated");
				grid.beforeComboPop("_grid_beforecombopop");
				grid.onCommand("_grid_oncommand");
				grid.onRowHover("_grid_onmousehover");
			}
			ns.fireEvent("onWidgetReady",[ns]);
		},
		_load:function(relate){
			var ns=this,grid=ns.grid;
			var post={
				ids:ns.properties.recordIds,
				field:ns.properties.field,
				relate:relate
			};
			AJAX.callService('xui/request',ns.properties.parentId,"inline_grid",post,function(rsp){
				if(!ns.isDestroyed()){
					ns.setProperties(rsp.data);
					grid.setHeader(rsp.data.gridHeaders);
					if(!ns.properties.setting.readonly){
						grid.setHotRowMode("show");
						if(!ns.properties.recordIds.length){
							grid.setEditable(false);
						}
					}
					grid.setActiveRow(null);
					grid.setUIValue(null,true);
					grid.setRows(rsp.data.rows);
				}

			},function() {
				grid.busy("正在处理 ...");
			},function(result){
				grid.free();
			});
		},
		_grid_oninithotrow:function (profile){
			//return {caption:"*", cells:[{caption:"<span style='color:#888'>(点击新增)</span>"}]};
		},
		_grid_beforehotrowadded:function (profile,row,leaveGrid){
			return row.cells[0].value!==""&&row.cells[0].value!==null;
		},
		_grid_aftercellupdated:function (profile,cell,options,isHotRow){
			if(isHotRow) {
				return;
			}
		},
		_grid_afterhotrowadded:function (profile, row){

		},
		_grid_beforecombopop:function(profile,cell,proEditor,pos,e,src){
			var ns = this,grid=profile.boxing(),elem = proEditor.boxing(),gprofile=profile;
			var col=grid.getColByCell(cell);
			if(col.type=="listbox"){
				var para = {field:grid.getColByCell(cell).id};
				if(!elem._isset) {
					AJAX.callService('xui/request', ns.properties.gridId, "get_select", para,function (rsp) {
						if (!elem.isDestroyed()) {
							elem.setItems(rsp.data).setValue(null, true);
							elem.onChange(function () {
								gprofile.box._checkNewLine(gprofile,true);
							});
							elem._isset=1;
						}
					}, function () {
						elem.setItems(["加载中 ..."], true);
					}, function () {
					});
				}
			}else if(col.type=="cmdbox"){
				var setting=ns.properties.gridSetting;
				xui.ModuleFactory.newCom(col.app, function(){
					if (!_.isEmpty(this)){
						this.setProperties({
							key:ns.properties.gridId,
							field:col.id,
							pos:elem.getRoot(),
							cmd:elem.getProperties("cmd"),
							value:elem.getUIValue(),
							setting:col
						});
						this.setEvents({
							onCancel:function(){
								if(!elem.isDestroyed()){
									elem.activate();
								}
							},
							onSelect:function(val,extra){
								if(!elem.isDestroyed()){
									elem.setUIValue(val.value);
									if(typeof(val.caption)==="string"){
										elem.setCaption(val.caption);
									}
									elem.activate();
									if(extra && _.isArr(extra)){
										_.arr.each(extra,function(exval){
											var setting=ns.properties.gridSetting;
											var ele=db.getUI(exval.id);
											ele.setUIValue(exval.cell.value);
											if(typeof(exval.cell.caption)==="string"){
												ele.setCaption(exval.cell.caption);
											}
										});
									}
									grid.updateCell(cell.id, {
										value: val.value,
										caption: val.caption
									}, false, false);
								}
							}
						});
						this.show();
					}

				});
			}
		},
		_grid_onmousehover: function (profile, row, hover, e, src) {
			if (profile.box.isHotRow(row)) {
				return
			}
			var ns = this,
				grid = profile.boxing(),
				btn = ns.btn_del;
			_.resetRun('TL_DEL', function () {
				if (hover) {
					var node = grid.getSubNodeInGrid("CELLS1",row.id).get(0);
					btn.setLeft(0);
					xui(node).append(btn);
					btn.setDisplay('');
					ns._curgrid = grid;
					ns._currowid = row.id
				} else {
					btn.setDisplay('none');
					ns.grid.append(btn);
					delete ns._curgrid;
					delete ns._currowid
				}
			})
		},
		_grid_oncommand:function (profile,cell,proEditor,src){
			var ns=this,ctrl=proEditor.boxing();
			var setting=ns.properties.gridSetting;
			xui.ModuleFactory.newCom(ctrl.getProperties("app"), function(){
				if (!_.isEmpty(this)){
					this.setProperties({
						key:ns.properties.gridId,
						field:ctrl.getDataField(),
						pos:ctrl.getRoot(),
						cmd:ctrl.getProperties("cmd"),
						value:ctrl.getUIValue(),
						setting:setting[ctrl.getDataField()],
						relate:ns._get_relate(profile)
					});
					this.setEvents({
						onCancel:function(){
							if(!ctrl.isDestroyed()){
								ctrl.activate();
							}
						},
						onSelect:function(val,extra){
							if(!ctrl.isDestroyed()){
								ctrl.setUIValue(val.value);
								if(typeof(val.caption)==="string"){
									ctrl.setCaption(val.caption);
								}
								ctrl.activate();
								if(extra && _.isArr(extra)){
									_.arr.each(extra,function(exval){
										var setting=ns.properties.gridSetting;
										var ele=db.getUI(exval.id);
										ele.setUIValue(exval.cell.value);
										if(typeof(exval.cell.caption)==="string"){
											ele.setCaption(exval.cell.caption);
										}
									});
								}
							}
						}
					});
					this.show();
				}

			});
			return false;
		},
		_remove_clicked: function (p, e) {
			var ns = this,
				btn = ns.btn_del;
			var grid=ns.grid;
			if (ns._curgrid && ns._currowid) {
				var curgrid = ns._curgrid,
					currowid = ns._currowid;
				xui.confirm('删除', '确定删除此行?', function () {
					btn.setDisplay('none');
					ns.grid.append(btn);
					AJAX.callService('xui/request',ns.properties.gridId,"delete",{ids:[currowid]},function(rsp){
						grid.removeRows([currowid]);
					},function(){
						xui.Dom.busy("正在处理 ...");
					},function(result){
						delete ns._curgrid;
						delete ns._currowid;
						xui.Dom.free();
					});

				}, null, null, null, ns.properties.dialog.getRoot().cssRegion())
			}
		},
		autoComplete:function(db){

		},
		_checkValid:function(){
			var ns=this,grid=ns.grid;
			return grid.checkValid();
		},
		_isDirty:function(){
			var ns=this,grid=ns.grid;
			return grid.isDirtied();
		},
		_save:function(callback,rsp){
			var ns=this,grid=ns.grid,error=0;
			if (ns._isDirty()){
				if (ns.properties.recordIds.length==0){
					if(rsp.data.length){
						ns.properties.recordIds=[rsp.data[0].id];
					}else{
						return false;
					}
				}
				var tmp={},post={
					ids:ns.properties.recordIds,
					data:[],
					grid:ns.properties.parentId,
					field:ns.properties.field

				};
				var data=grid.getDirtied();
				_.each(data,function(d){
					if(!_.isSet(tmp[d.rowId])) {
						tmp[d.rowId]={};
					}
					tmp[d.rowId][d.colId]=d.value;
				});
				_.each(tmp,function(d,i){
					post.data.push({
						id:[parseInt(i,10)],
						fields:d,
						rowid:i
					});
				});
				AJAX.callService('xui/request', ns.properties.gridId, "inline_save", post, function (rsp) {
					if (rsp.data == 1 || typeof(rsp.data) === 'object') {
						_.arr.each(rsp.data.rows,function(row){
							if(!row.error){
								if(row.id){
									grid.updateRow(row.rowid,{id:row.id});
									grid.resetRowValue(row.id);
									grid.updateCellByRowCol(row.id, 0, {dirty:false})
								}else{
									grid.resetRowValue(row.rowid);
								}
							}else{
								error=1;
							}
						});
					} else {
						xui.message(rsp);
					}

				}, function(){
					ns.grid.busy("正在处理 ...");
				}, function(){
					if (ns.grid)
						ns.grid.free();
					callback(error);
				});
				return 1;
			}else{
				return 0;
			}
		}
	}
});
