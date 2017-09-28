Class('App.Validate', 'xui.Module',{
	Instance:{
		autoDestroy : true,
		properties : {},
		initialize : function(){
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
				.setLeft(120)
				.setTop(100)
				.setWidth(640)
				.setHeight(480)
				.setCaption("检查结果")
				.setMinBtn(false)
			);
			host.mainDlg.append(
				xui.create("xui.UI.Input")
				.setHost(host,"result")
				.setDock("fill")
				.setLeft(16)
				.setTop(15)
				.setWidth(220)
				.setHeight(120)
				.setMultiLines(true)
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
				.setHost(host,"btnClose")
				.setLeft(276)
				.setTop(11)
				.setWidth(70)
				.setCaption("关闭")
				.onClick("_ctl_sbutton486_onclick")
				);
			return children;
			// ]]Code created by CrossUI RAD Studio
		},
		customAppend : function(parent, subId, left, top){
			return false;
		},
		events:{"onRender":"_com_onrender"},
		_com_onrender:function (com, threadid){
			var ns=this;
			var paras={
				filters:ns.properties._filter,
				search:ns.properties._search,
				sidx:ns.properties._sidx,
				sord:ns.properties._sord,
				sub:ns.properties._sub
			};
			AJAX.callService('xui/request',ns.properties.gridId,"data_validate",paras,function(rsp){
				ns.result.setUIValue(rsp.data);
			},function(){
				ns.mainDlg.busy("正在处理 ...");
			},function(result){
				ns.mainDlg.free();
			}); 
		},
		_ctl_sbutton486_onclick:function(){
			var ns=this;
			ns.mainDlg.close(false);
		}
	}
});
