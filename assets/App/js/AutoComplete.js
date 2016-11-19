Class('App.AutoComplete', 'xui.Com',{
	Instance:{
		autoDestroy : true,
		properties : {},
		initialize : function(){
		},
		iniComponents : function(){
			var host=this, children=[], append=function(child){children.push(child.get(0));};
			
			append((new xui.UI.Panel())
				.setHost(host,"mainPanel")
				.setDock("none")
				.setLeft(0)
				.setTop(0)
				.setWidth(400)
				.setHeight(400)
				.setZIndex(1)
				.setCaption("选择窗口")
				.setCloseBtn(true)
				.beforeClose("_mainpanel_beforeclose")
			);
			
			host.mainPanel.append((new xui.UI.TreeView())
				.setHost(host,"grid")
			);
			
			host.mainPanel.append((new xui.UI.Block())
				.setHost(host,"ctl_block8")
				.setDock("top")
				.setHeight(30)
			);
			
			host.ctl_block8.append((new xui.UI.SButton())
				.setHost(host)
				.setTop(3)
				.setWidth(80)
				.setRight(10)
				.setImage("@xui_ini.appPath@image/refresh.png")
				.setCaption("刷新")
				.onClick("_ctl_sbutton1_onclick")
			);
			
			host.ctl_block8.append((new xui.UI.PageBar())
				.setHost(host,"pagebar")
				.setTop(3)
				.setRight(100)
				.setCaption("页数:")
				.onClick("_pagebar_onclick")
			);
			
			host.mainPanel.append((new xui.UI.Block())
				.setHost(host,"ctl_block9")
				.setDock("top")
				.setHeight(30)
			);
			
			host.ctl_block9.append((new xui.UI.ComboInput())
				.setHost(host,"filter")
				.setType("getter")
				.setDock("fill")
				.setLabelSize(50)
				.setLabelCaption("输入")
				.setShowDirtyMark(false)
				.beforeComboPop("_filter_beforeComboPop")
				.onChange("_filter_onchange")
			);
			
			host.mainPanel.append((new xui.UI.Block())
				.setHost(host,"ctl_block10")
				.setDock("bottom")
				.setHeight(40)
			);
			host.ctl_block10.append((new xui.UI.SButton())
				.setHost(host)
				.setTop(10)
				.setWidth(80)
				.setLeft(25)
				.setCaption("确定")
				.onClick("_ctl_sbutton2_onclick")
			);
			host.ctl_block10.append((new xui.UI.SButton())
				.setHost(host)
				.setTop(10)
				.setWidth(80)
				.setRight(25)
				.setCaption("关闭")
				.onClick("_ctl_sbutton3_onclick")
			);
			
			return children;
		},
		_fillGrid:function(data){
			var ns=this,grid=ns.grid;
			grid.setItems(data.items);
			grid.activate();
		},
		_mainpanel_beforeclose:function (profile){
			this.fireEvent("onCancel");
		},
		customAppend : function(parent, subId, left, top){
			var ns=this, root=ns.mainPanel,
				domId=root.getDomId();
			root.getRoot().popToTop(ns.properties.pos);
			root.getRoot().setBlurTrigger(domId, function(){
				ns.fireEvent("onCancel");
				ns.destroy(); 
			});
			xui.Event.keyboardHook("esc", false, false, false,function(){
				ns.fireEvent("onCancel");
				ns.destroy(); 
			},null,null,domId);
			ns.filter.setUIValue(ns.properties.value);
			ns.loadGridData(1);
			return true;
		},
		loadGridData:function(curPage){
			var ns=this, 
				grid=ns.grid;
			this._curPage=curPage;
			AJAX.callService(ns.properties.key,"auto_complete",{
				field:ns.properties.field,
				page:curPage,
				like:ns.like,
				size:20
			},function(rsp){
				if(!ns.isDestroyed()){
					ns.pagebar.setValue("1:"+curPage+":"+( Math.ceil(parseInt(rsp.data.count,10)/20) ),true);
					ns._fillGrid(rsp.data);
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
		_filter_beforeComboPop:function(profile, pos){
			var ns=this,ctrl=profile.boxing();
			ctrl.setUIValue("",true);
		},
		_filter_onchange:function(profile, oldValue, newValue, force, tag){
			var ns=this,ctrl=profile.boxing(),grid=ns.grid;
				this.like=ctrl.getUIValue();
				this.loadGridData(1);
		},
		_filter:function(grid,items,reg){
			var ns=this,find=false;
			for(i in items){
				var o=items[i];
				if ((!(typeof o.sub == "object" && o.sub.length && ns._filter(grid, o.sub, reg)))
						&& (!reg.test(o.id) && !reg.test(o.caption) && !reg.test(o.key))){
					grid.hideItems(o.id);
				}else{
					grid.showItems(o.id);
					find=true;
				}
			}
			return find;
		},
		_ctl_sbutton1_onclick:function (profile, e, src, value){
			this.loadGridData(this._curPage);
		},
		_ctl_sbutton2_onclick:function (profile, e, src, value){
			var ns=this,
				grid=ns.grid;
			value=grid.getUIValue(true);
			if(typeof value!="undefined"){
				var item=grid.getItemByItemId(value);
				ns.fireEvent("onSelect",[item.caption]);
			}
			ns.destroy();
		},
		_ctl_sbutton3_onclick:function(){
			var ns=this;
			ns.fireEvent("onCancel");
			ns.destroy(); 
		},
		_pagebar_onclick:function (profile, page){
			this.loadGridData(page);
		}
	}
});
