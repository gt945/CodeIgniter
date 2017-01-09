Class('App.QKZX.DeliveryByCID', 'xui.Module',{
	autoDestroy : true,
	Instance:{
		initialize : function(){
			var ns=this;
			ns._dataFilter=null;
		},
		iniComponents : function(){
			var host=this, children=[], append=function(child){children.push(child.get(0));};
			append((new xui.DataBinder())
				.setHost(host,"databinder")
				.setName("databinder")
				.setData()
			);
			append((new xui.UI.Dialog())
				.setHost(host,"mainDlg")
				.setLeft(25)
				.setTop(19)
				.setWidth(480)
				.setHeight(124)
				.setResizer(true)
				.setOverflow("hidden")
				.setCaption("确认发货信息")
				.setMinBtn(false)
				// .setMaxBtn(false)
			);

			host.mainDlg.append((new xui.UI.Block())
				.setHost(host,"ctl_block")
				.setBorderType("inset")
				.setDock("fill")
				.setOverflow("overflow-x:hidden;overflow-y:auto")
			);

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
			var ns=this,pgrid=ns.properties.editor.grid;
			var editor_prop=ns.properties.editor.properties;
			var db=ns.databinder,data=db.getData();
			var row=pgrid.getActiveRow();
			if (row) {
				_.arr.each(['CID'],function(f, i){
					var cell=pgrid.getCellbyRowCol(row.id, f);
					var ele=_.unserialize(editor_prop.gridSetting[f].form);
					ns.ctl_block.append(ele
						.setHost(ns,'form_'+i)
						.setLeft(15)
						.setTop(30*i+15)
						.setWidth(410)
						.setDataBinder("databinder")
						.setDataField(f)
					);
					data[f]={value:cell.value,caption:cell.caption};
				});
				db.setData(data).updateDataToUI();
			}else{
				xui.message("未选择条目!");
				ns.mainDlg.close(true);
			}
		},
		_ctl_sbutton486_onclick:function() {
			this.mainDlg.close(true);
		},
		_ctl_sbutton14_onclick:function(){
			var ns=this,db=ns.databinder,data=db.getData();
			if (!_.isEmpty(data)) {
				AJAX.callService('QKZX/request',null,"delivery_by_cid",data,function (rsp) {
					if (typeof(rsp.data)==='string'){
						xui.message(rsp.data);
					}else{
						ns.fireEvent("refreshGrid");
						xui.message("发货成功!");
						ns.mainDlg.close(false);
					}
				},function(){
					ns.mainDlg.busy("正在处理 ...");
				},function(){
					if(ns.mainDlg)
						ns.mainDlg.free();
				});
			}
		}
	}
});
