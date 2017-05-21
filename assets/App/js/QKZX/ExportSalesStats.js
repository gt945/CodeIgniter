Class('App.QKZX.ExportSalesStats', 'xui.Module',{
	autoDestroy : true,
	Instance:{
		initialize : function(){
			var ns=this;
			ns._dataFilter=null;
		},
		iniComponents : function(){
			// [[Code created by CrossUI RAD Studio
			var host=this, children=[], append=function(child){children.push(child.get(0));};
			
			append(
				xui.create("xui.UI.Dialog")
				.setHost(host,"mainDlg")
				.setLeft(190)
				.setTop(120)
				.setWidth(490)
				.setHeight(390)
				.setCaption("选择要导出的期刊")
				.setMinBtn(false)
				.setMaxBtn(false)
				.setRestoreBtn(false)
				.onShow("_dialog_onshow")
			);
			
			host.mainDlg.append(
				xui.create("xui.UI.Pane")
				.setHost(host,"xui_ui_pane12")
				.setDock("fill")
				);
			
			host.xui_ui_pane12.append(
				xui.create("xui.UI.TreeGrid")
				.setHost(host,"grid")
				.setRowHandlerWidth(18)
				.setTreeMode(false)
				.onRowHover("_grid_onmousehover")
				);
			
			host.mainDlg.append(
				xui.create("xui.UI.Pane")
				.setHost(host,"xui_ui_pane11")
				.setDock("bottom")
				.setHeight(35)
				);
			
			host.xui_ui_pane11.append(
				xui.create("xui.UI.SButton")
				.setHost(host,"xui_ui_sbutton3")
				.setRight(40)
				.setTop(8)
				.setWidth(100)
				.setCaption("添加更多")
				.onClick("_ctl_sbutton3_onclick")
				);
			
			host.xui_ui_pane11.append(
				xui.create("xui.UI.SButton")
				.setHost(host,"xui_ui_sbutton4")
				.setLeft(40)
				.setTop(8)
				.setWidth(100)
				.setCaption("统计")
				.onClick("_ctl_sbutton4_onclick")
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
		customAppend : function(parent, subId, left, top){
			if(this.mainDlg)
				this.mainDlg.showModal(parent, left, top);
			return true;
		},
		events:{"onRender":"_com_onrender"},
		_com_onrender:function (com, threadid){
			
			var ns=this,grid=ns.grid,pgrid=ns.properties.editor.grid;
			var editor_prop=ns.properties.editor.properties;
			var headers=editor_prop.gridHeaders;
			grid.setHeader(editor_prop.gridHeaders);
		},
		_dialog_onshow:function(profile){
			var ns=this,grid=ns.grid,pgrid=ns.properties.editor.grid;
			var rows_id=pgrid.getUIValue();
			var rows=grid.getRows();
			if (rows_id) {
				var values=rows_id.split(";");
				if (values.length>0){
					_.arr.each(values, function(id){
						if(_.arr.subIndexOf(rows,'id',id)<0){
							grid.insertRows(pgrid.getRowbyRowId(id));
						}
					});
				}
			}
		},
		_ctl_sbutton3_onclick:function(profile, e, src, value){
			this.mainDlg.hide();
		},
		_grid_onmousehover:function(profile,row,hover,e,src) {
			if (profile.box.isHotRow(row)) {
				return;
			}
			var ns = this,
				grid = profile.boxing(),
				btn = ns.btn_del;
			_.resetRun('TL_DEL',function(){
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
		_remove_clicked:function(p,e) {
			var ns = this,
				btn = ns.btn_del;
			var grid=ns.grid;
			if (ns._curgrid && ns._currowid) {

				var curgrid = ns._curgrid,
					currowid = ns._currowid;
				xui.confirm('删除', '确定删除此行?', function () {
					btn.setDisplay('none');
					ns.grid.append(btn);
					grid.removeRows([currowid]);

				}, null, null, null, ns.mainDlg.getRoot().cssRegion())
			}
		},
		_ctl_sbutton4_onclick:function(p,e){
			var ns=this,grid=ns.grid,rows=grid.getRows(),post=[],fields=["JID","No"];
			_.arr.each(rows,function(row){
				var data={};
				_.arr.each(fields,function(f){
					data[f]=grid.getCellbyRowCol(row.id,f,"min");
				});
				post.push(data);
			});
			if (!_.isEmpty(post)) {
				xui.Dom.submit(SITEURL+'data/sales_stats',{data:post},'post');
			}
		}
	}
});

