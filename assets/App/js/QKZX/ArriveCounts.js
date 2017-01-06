Class('App.QKZX.ArriveCounts', 'xui.Module',{
	Instance:{
		autoDestroy : true,
		properties : {},
		initialize : function(){
		},
		iniComponents : function(){
			var host=this, children=[], append=function(child){children.push(child.get(0));};

			append(
				(new xui.UI.Panel())
					.setHost(host,"mainPanel")
					.setDock("none")
					.setLeft(0)
					.setTop(0)
					.setWidth(640)
					.setHeight(480)
					.setZIndex(1)
					.setCaption("订单信息")
					.setCloseBtn(true)
					.beforeClose("_mainpanel_beforeclose")
			);
			host.mainPanel.append(
				(new xui.UI.TreeGrid())
					.setHost(host,"grid")
					.setRowHandlerWidth(27)
					.setRowHandler(false)
					.setTreeMode(false)
			);
			host.mainPanel.append(
				(new xui.UI.Block())
					.setHost(host,"ctl_block")
					.setDock("top")
					.setHeight(30)
			);
			host.ctl_block.append(
				(new xui.UI.SButton())
					.setHost(host)
					.setTop(3)
					.setWidth(80)
					.setRight(10)
					.setImage("@xui_ini.appPath@image/refresh.png")
					.setCaption("刷新")
					.onClick("_ctl_sbutton1_onclick")
			);
			host.ctl_block.append(
				(new xui.UI.PageBar())
					.setHost(host,"pagebar")
					.setTop(3)
					.setRight(100)
					.setCaption("页数:")
					.onClick("_pagebar_onclick")
			);
			
			host.mainPanel.append(
				(new xui.UI.Block())
					.setHost(host,"ctl_block10")
					.setDock("bottom")
					.setHeight(40)
			);
			host.ctl_block10.append(
				(new xui.UI.ComboInput())
					.setHost(host,"total")
					.setLeft(10)
					.setTop(10)
					.setLabelSize(50)
					.setLabelCaption("合计:")
					.setType("none")
					.setReadonly(true)
					.setShowDirtyMark(false)
					.setCustomStyle({
						"LABEL" : {
							"color" : "#000000"
						}
					})
			);
			host.ctl_block10.append(
				(new xui.UI.SButton())
					.setHost(host)
					.setTop(10)
					.setWidth(80)
					.setRight(25)
					.setCaption("关闭")
					.onClick("_ctl_sbutton3_onclick")
			);
			return children;
		},
		_fillGrid:function(headers,rows){
			var ns=this,grid=ns.grid;
			grid.setHeader(headers);
			grid.setRows(rows);
			grid.activate();
		},
		customAppend : function(parent, subId, left, top){
			var ns=this, root=ns.mainPanel,
				domId=root.getDomId();
			root.getRoot().popToTop(ns.properties.pos);
			xui.Event.keyboardHook("esc", false, false, false,function(){
				ns.destroy();
			},null,null,domId);
			this.loadGridData(1);
			return true;
		},
		loadGridData:function(curPage){
			var ns=this,
				grid=ns.grid;
			this._curPage=curPage;
			var paras={
				field:ns.properties.field,
				page:curPage,
				size:20,
				relate:ns.properties.relate
			};

			AJAX.callService("QKZX/request", null, "get_arrive_counts", paras, function(rsp){
				if(!ns.isDestroyed()){
					ns.pagebar.setValue("1:"+curPage+":"+( Math.ceil(parseInt(rsp.data.count,10)/20) ),true);
					ns._fillGrid(rsp.data.headers,rsp.data.rows);
					ns.total.setUIValue(rsp.data.total);
				}
			}, function(){
				ns.mainPanel.busy();
			},function(result){
				ns.mainPanel.free();
			});

		},
		_ctl_sbutton1_onclick:function (profile, e, src, value){
			this.loadGridData(this._curPage);
		},
		_ctl_sbutton3_onclick:function(){
			var ns=this;
			ns.destroy();
		},
		_pagebar_onclick:function (profile, page){
			this.loadGridData(page);
		}
	}
});
