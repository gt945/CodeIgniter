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
				.setEditable(true)
				.setRowNumbered(true)
				.setRowHandlerWidth(26)
				.setTreeMode(false)
				.onInitHotRow("_grid_oninithotrow")
				.beforeHotRowAdded("_grid_beforehotrowadded")
				.afterHotRowAdded("_grid_afterhotrowadded")
				.afterCellUpdated("_grid_aftercellupdated")
				.beforeComboPop("_grid_beforecombopop")
				.onCommand("_grid_oncommand")
			);

			return children;
			// ]]Code created by CrossUI RAD Studio
		},
		customAppend : function(parent, subId, left, top){
			return false;
		},
		events:{"onRender":"_com_onrender"},
		_com_onrender:function (com, threadid){
			var ns=this,grid=ns.grid;
			AJAX.callService('QKZX/request',null,"publishnotifydetails_grid",null,function(rsp){
				ns.setProperties(rsp.data);
				grid.setHeader(ns.properties.gridHeaders);
				grid.setHotRowMode("show");
				ns.loadGridData(1);
			},function() {
				grid.busy("正在处理 ...");
			},function(result){
				grid.free();
			});
		},
		loadGridData:function(curPage){
			var ns=this, grid=ns.grid;
			var pageSize=ns.properties.pageSize;

			// ns._curPage=curPage;
			// ns._fetch_data(function(rsp){
			//	 if(!ns.isDestroyed()){
			//		 ns.pagebar.setValue("1:"+curPage+":"+( Math.ceil(parseInt(rsp.data.count,10)/pageSize) ),true);
			//		 ns._fillGrid(rsp.data.rows);
			//	 }
			// });
		},
		_grid_oninithotrow:function (profile){
			//return {caption:"*", cells:[{caption:"<span style='color:#888'>(点击新增)</span>"}]};
		},
		_grid_beforehotrowadded:function (profile,row,leaveGrid){
			return row.cells[0].value !== "";
		},
		_grid_aftercellupdated:function (profile,cell,options,isHotRow){
			// if(isHotRow) {
			//	 return;
			// }
		},
		_grid_afterhotrowadded:function (profile, row){

		},
		_grid_beforecombopop:function(profile,cell,proEditor,pos,e,src){
			var ns = this,grid=profile.boxing(),elem = proEditor.boxing(),gprofile=profile;
			var col=grid.getColByCell(cell);
			if(col.type=="listbox"){
				var para = {field:grid.getColByCell(cell).id};
				if(!elem._isset) {
					AJAX.callService('xui/request', ns.properties.gridId, "get_select", para,
						function (rsp) {
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
			}
		},
		_grid_oncommand:function (profile,cell,proEditor,src){
			var ns=this,ctrl=proEditor.boxing();
			var setting=ns.properties.gridSetting;
			debugger;
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
		}
	}
});
