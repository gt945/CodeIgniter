Class('App.QKZX.PaperStockDetail', 'xui.Module',{
	Instance:{
		autoDestroy : true,
		properties : {
			filterForm:null
		},
		initialize : function(){
			var ns=this;
			ns._search=false;
			ns._filters={};
			ns._defaultrule=null;
		},
		iniComponents : function(){
			var host=this, children=[], append=function(child){children.push(child.get(0));};
			
			append(
				(new xui.UI.Dialog())
				.setHost(host,"mainDlg")
				.setDock("none")
				.setLeft(0)
				.setTop(0)
				.setWidth(800)
				.setHeight(600)
				.setZIndex(1)
				.setCaption("选择窗口")
				.setCloseBtn(true)
			);
			
			host.mainDlg.append(
				(new xui.UI.TreeGrid())
				.setHost(host,"grid")
				.setRowHandlerWidth(27)
				.setRowHandler(true)
				.setTreeMode(false)
			);

			host.mainDlg.append(
				(new xui.UI.Block())
				.setHost(host,"ctl_block8")
				.setDock("top")
				.setHeight(30)
			);

			host.ctl_block8.append(
				(new xui.UI.ToolBar())
					.setHost(host,"toolbar")
					.setItems([{"id":"grp1", "sub":[{"id":"null","caption":""}], "caption":"grp1"}])
					.onClick("_toolbar_onclick")
			);
			host.ctl_block8.append(
				(new xui.UI.PageBar())
				.setHost(host,"pagebar")
				.setTop(3)
				.setRight(100)
				.setCaption("页数:")
				.onClick("_pagebar_onclick")
			);

			host.ctl_block8.append(
				(new xui.UI.SButton())
				.setHost(host)
				.setTop(3)
				.setWidth(80)
				.setRight(10)
				.setImage("@xui_ini.appPath@image/refresh.png")
				.setCaption("刷新")
				.onClick("_ctl_sbutton1_onclick")
			);
			
			host.mainDlg.append(
				(new xui.UI.Block())
				.setHost(host,"ctl_block9")
				.setDock("bottom")
				.setHeight(40)
			);
			
			host.ctl_block9.append(
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
		events:{"onRender":"_com_onrender", "beforeCreated":"_beforeCreated"},
		customAppend : function(parent, subId, left, top){
			this.mainDlg.showModal(parent, left, top);
			return true;
		},
		_com_onrender:function(){
			var ns=this, grid=ns.grid;
			var row=ns.properties.editor.grid.getActiveRow();
			grid.setHeader(ns.properties.gridHeaders);
			ns.toolbar.setItems(_.unserialize(ns.properties.gridToolBarItems));
			if (row) {
				ns._search=true;
				ns._defaultrule = {"data":row.id, "op":"eq", "field":"PaperStyleID"};
				ns._filters={
					groupOp:"AND",
					rules:[
						ns._defaultrule
					]
				};
			}
			ns.loadGridData(1);
		},
		_beforeCreated:function(com, threadid){
			var ns=this;
			xui.Thread.suspend(threadid);
			var callback=function(/**/){
				xui.Thread.resume(threadid);
			};
			AJAX.callService('xui/request',"纸张出入库记录", "grid", null, function (rsp) {
				ns.setProperties(rsp.data);
			}, function(){
			}, function(){
				callback();
			});
		},
		_fillGrid:function(rows){
			var ns=this,grid=ns.grid;
			grid.setRows(rows);
			grid.activate();
		},
		loadGridData:function(curPage){
			var ns=this, 
				grid=ns.grid;
			this._curPage=curPage;
			AJAX.callService('xui/request',ns.properties.gridId,"getlist",{
				field:ns.properties.field,
				page:curPage,
				size:20,
				filters:ns._filters,
				search:ns._search
			},function(rsp){
				if(!ns.isDestroyed()){
					if(typeof(rsp.data)=="string"){
						xui.message(rsp.data);
					}else{
						ns.pagebar.setValue("1:"+curPage+":"+( Math.ceil(parseInt(rsp.data.count,10)/20) ),true);
						ns._fillGrid(rsp.data.rows);
						ns.data = rsp.data;
					}
				}
			},function(){
				grid.busy("正在处理 ...");
			},function(result){
				grid.free();
				if(result=='fail'){
					if(!ns.isDestroyed())
						ns.destroy(); 
				}
			});   
		},
		_ctl_sbutton1_onclick:function (profile, e, src, value){
			this.loadGridData(this._curPage);
		},
		_ctl_sbutton3_onclick:function(){
			var ns=this;
			ns.fireEvent("onCancel");
			ns.destroy(); 
		},
		_toolbar_onclick:function (profile, item, group, e, src){
			var ns = this,ctrl=profile.boxing();
			switch(item.id){
				case "filter":
					ns._openFilter();
					break;
			}
		},
		_openFilter:function(){
			var ns = this;
			if (ns.properties.filterForm){
				ns.properties.filterForm.mainDlg.show(null,true);
			}else{
				xui.ModuleFactory.newCom(ns.properties.gridFilter,function(){
					if (!_.isEmpty(this)){
						ns.properties.filterForm=this;
						this.show();
					}
				},null,ns.properties,{
					onSelect:function(filters){
						if(filters&&filters.rules.length){
							filters.rules.push(ns._defaultrule);
						}
						ns._filters=filters?filters:ns._filters;
						if (ns._filters){
							ns._search=true;
						}
						ns.loadGridData(1);
					}
				});
			}

		},
		_pagebar_onclick:function (profile, page){
			this.loadGridData(page);
		}
	}
});