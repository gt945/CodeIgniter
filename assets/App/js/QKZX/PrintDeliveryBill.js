Class('App.QKZX.PrintDeliveryBill', 'xui.Module',{
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
				.setHeight(172)
				.setResizer(true)
				.setOverflow("hidden")
				.setCaption("打印客户取刊单")
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
				.setCaption("确定")
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
				var cid_id;
				_.arr.each(['BatchID','CID','DeliveStatus'],function(f, i){
					var cell=pgrid.getCellbyRowCol(row.id, f);
					var ele=_.unserialize(editor_prop.gridSetting[f].form);
					ns.ctl_block.append(ele
						.setHost(ns,'form_'+i)
						.setLeft(15)
						.setTop(30*i+15)
						.setWidth(410)
						.setDataBinder("databinder")
						.setDataField(f)
						.setReadonly(false)
					);
					data[f]={value:cell.value,caption:cell.caption};
					if(f=='CID'){
						cid_id=cell.value;
					}
				});
				
				var rows_id=pgrid.getUIValue();
				if (rows_id) {
					var batch=[];
					var values=rows_id.split(";");
					if (values.length>0){
						_.arr.each(values, function(id){
							var cell=pgrid.getCellbyRowCol(id,'BatchID');
							var cid=pgrid.getCellbyRowCol(id,'CID');
							if(cell&&cid&&cid.value==cid_id&&_.arr.indexOf(batch,cell.value)<0)
								batch.push(cell.value);
						});
						data['BatchID'].value=batch.join(',');
					}
				}
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
				xui.Dom.submit(SITEURL+'data/delivery_bill',{CID:data.CID.value,BatchIDs:data.BatchID.value,DeliveStatus:data.DeliveStatus.value},'post');
			}
		}
	}
});
