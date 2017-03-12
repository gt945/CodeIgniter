Class('App.QKZX.ExportPaperUseDetail', 'xui.Module',{
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
				xui.create("xui.DataBinder")
				.setHost(host,"databinder")
				.setName("databinder")
			);
			
			append(
				xui.create("xui.UI.Dialog")
				.setHost(host,"mainDlg")
				.setLeft(25)
				.setTop(19)
				.setWidth(480)
				.setHeight(184)
				.setCaption("打印用纸情况表")
				.setMinBtn(false)
				.setOverflow("hidden")
			);
			
			host.mainDlg.append(
				xui.create("xui.UI.Block")
				.setHost(host,"ctl_block")
				.setDock("fill")
				.setBorderType("inset")
				.setOverflow("overflow-x:hidden;overflow-y:auto")
				);
			
			host.ctl_block.append(
				xui.create("xui.UI.ComboInput")
				.setHost(host,"paper")
				.setLeft(80)
				.setTop(15)
				.setWidth(315)
				.setHeight(24)
				.setLabelSize(120)
				.setReadonly(true)
				.setLabelCaption("纸名")
				.setType("none")
				.setDataBinder("databinder")
				.setDataField("paper")
				.setCustomStyle({
					"LABEL" : {
						"text-align" : "left"
					}
				}
				)
				);
			
			host.ctl_block.append(
				xui.create("xui.UI.ComboInput")
				.setHost(host,"stime")
				.setLeft(80)
				.setTop(45)
				.setWidth(315)
				.setHeight(24)
				.setLabelSize(120)
				.setLabelCaption("起始时间")
				.setType("date")
				.setDateEditorTpl("yyyy-mm-dd")
				.setDataBinder("databinder")
				.setDataField("stime")
				.setCustomStyle({
					"LABEL" : {
						"text-align" : "left"
					}
				}
				)
				);
			
			host.ctl_block.append(
				xui.create("xui.UI.ComboInput")
				.setHost(host,"etime")
				.setLeft(80)
				.setTop(75)
				.setWidth(315)
				.setLabelSize(120)
				.setLabelCaption("终止时间")
				.setType("date")
				.setDateEditorTpl("yyyy-mm-dd")
				.setDataBinder("databinder")
				.setDataField("etime")
				.setCustomStyle({
					"LABEL" : {
						"text-align" : "left"
					}
				}
				)
				);
			
			host.mainDlg.append(
				xui.create("xui.UI.Block")
				.setHost(host,"xui_ui_block4")
				.setDock("bottom")
				.setHeight(35)
				.setBorderType("none")
				);
			
			host.xui_ui_block4.append(
				xui.create("xui.UI.SButton")
				.setHost(host,"btnSave")
				.setLeft(140)
				.setTop(5)
				.setWidth(70)
				.setCaption("确定")
				.onClick("_ctl_sbutton14_onclick")
				);
			
			host.xui_ui_block4.append(
				xui.create("xui.UI.SButton")
				.setHost(host,"btnClose")
				.setLeft(270)
				.setTop(5)
				.setWidth(70)
				.setCaption("取消")
				.onClick("_ctl_sbutton486_onclick")
				);
			
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
			var ns=this,pgrid=ns.properties.editor.grid;
			var row=pgrid.getActiveRow();
			if (row) {
				var cell=pgrid.getCellbyRowCol(row.id, "PaperStyle");
				ns.paper.setValue(cell.value);
			}else{
				xui.message("未选择条目!");
				ns.mainDlg.close(true);
			}
		},
		_ctl_sbutton486_onclick:function() {
			this.mainDlg.close(true);
		},
		_ctl_sbutton14_onclick:function(){
			var ns=this,db=ns.databinder,data=db.getDirtied();
			if (!_.isEmpty(data)) {
				debugger;
			}
		}
	}
});
