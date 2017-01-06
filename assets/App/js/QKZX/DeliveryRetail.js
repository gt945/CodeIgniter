Class('App.QKZX.DeliveryRetail', 'xui.Module',{
	autoDestroy : true,
	Instance:{
		initialize : function(){
			var ns=this;
			ns._dataFilter=null;
		},
		iniComponents : function(){
			var host=this, children=[], append=function(child){children.push(child.get(0));};
			append((new xui.UI.Dialog())
				.setHost(host,"mainDlg")
				.setLeft(25)
				.setTop(19)
				.setWidth(800)
				.setHeight(480)
				.setResizer(true)
				.setResizerProp({vertical:true,horizontal:true,minHeight:300,minWidth:300})
				.setOverflow("hidden")
				.setCaption("确认发货信息")
				.setMinBtn(false)
				.setMaxBtn(false)
				.onResize("_dialog_resize")
			);

			host.mainDlg.append((new xui.UI.Block())
				.setHost(host,"ctl_block")
				.setBorderType("inset")
				.setDock("fill")
				.setOverflow("overflow-x:hidden;overflow-y:auto")
			);
			host.ctl_block.append((new xui.UI.TreeGrid())
				.setHost(host,"grid")
				.setRowHandlerWidth(18)
				.setTreeMode(false)
				.onRowHover("_grid_onmousehover")
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

			host.mainDlg.append((new xui.UI.Block())
				.setHost(host,"xui_ui_block4")
				.setHeight(35)
				.setDock("bottom")
				.setBorderType("none")
			);

			host.xui_ui_block4.append((new xui.UI.SButton())
				.setHost(host,"btnSave")
				.setLeft(480/ 2 - 100)
				.setTop(5)
				.setWidth(70)
				.setCaption("确认发货")
				.onClick("_ctl_sbutton14_onclick")
			);

			host.xui_ui_block4.append((new xui.UI.SButton())
				.setHost(host,"btnClose")
				.setLeft(480 / 2 + 30)
				.setTop(5)
				.setWidth(70)
				.setCaption("取消")
				.onClick("_ctl_sbutton486_onclick")
			);

			return children;
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
			var i=_.arr.subIndexOf(headers, "id", "RealCounts");
			if (i>=0){
				// headers[i].editable=false;
				// headers[i].cellStyle=null;
				headers[i].caption="发货数量";
			}
			grid.setHeader(editor_prop.gridHeaders);
			var rows_id=pgrid.getUIValue();
			if (rows_id) {
				var values=rows_id.split(";");
				if (values.length>0){
					var rows=[];
					_.arr.each(values, function(id){
						rows.push(pgrid.getRowbyRowId(id));
					});
					grid.setRows(rows);
				}
			}else{
				xui.message("未选择条目!");
				ns.mainDlg.close(true);
			}

		},
		_ctl_sbutton486_onclick:function (profile, e, src, value){
			this.mainDlg.close(true);
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
					var node = grid.getSubNode('SCROLL').get(0);
					btn.setLeft(0);
					xui(src).append(btn);
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
					grid.removeRows([currowid]);

				}, null, null, null, ns.mainDlg.getRoot().cssRegion())
			}
		},
		_ctl_sbutton14_onclick:function(){
			var ns=this,grid=ns.grid;
			var rows=grid.getRows();
			var datas=[];
			_.arr.each(rows,function(row){
				var data={};
				_.arr.each(['CID','JID','Year','No','Counts'], function(i){
					var cell=grid.getCellbyRowCol(row.id,i);
					data[i]=cell.value;
				});
				datas.push(data);
			});
			var paras={
				data:datas
			};
			AJAX.callService('QKZX/request',null,"delivery_retail",paras,function (rsp) {
				if (typeof(rsp.data)==='string') {
					xui.message(rsp.data);
				} else {
					ns.fireEvent("refreshGrid");
					xui.message("发货成功!");
					ns.mainDlg.close(false);
				}
			}, function(){
				ns.mainDlg.busy("正在处理 ...");
			}, function(){
				if (ns.mainDlg)
					ns.mainDlg.free();
			});
		},
		_dialog_resize:function(profile,w,h){
			var ns=this;
			ns.btnSave.setLeft(w/2-100);
			ns.btnClose.setLeft(w/2+30);
		}
	}
});
