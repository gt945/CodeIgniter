Class('App.UserGroupSelect', 'xui.Module',{
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
				.onDblclick("_grid_ondblclick")
				.onItemSelected("_grid_onitemselected")
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
				.setLabelCaption("过滤")
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
		_fillGrid:function(items){
			var ns=this,grid=ns.grid;
			grid.setItems(items);
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
			ns.grid.setSelMode(ns.properties.mode);
			ns.loadGridData(1);
			return true;
		},
		loadGridData:function(curPage){
			var ns=this, 
				grid=ns.grid;
			this._curPage=curPage;
			AJAX.callService('system/request',null,"user_group",{
				field:ns.properties.field,
				page:curPage,
				like:ns.like,
				size:20,
				relate:ns.properties.relate
			},function(rsp){
				if(!ns.isDestroyed()){
					ns._fillGrid(rsp.data.items);
					grid.toggleNode(null,true,true);
					if (ns.properties.value){
						grid.setUIValue(ns.properties.value);
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
		_filter_beforeComboPop:function(profile, pos){
			var ns=this,ctrl=profile.boxing();
			ctrl.setUIValue("",true);
		},
		_filter_onchange:function(profile, oldValue, newValue, force, tag){
			var ns=this,ctrl=profile.boxing(),grid=ns.grid;
			if(newValue!=oldValue) {
				var items = grid.getItems();
				ns._filter(grid, items, (new RegExp(ctrl.getUIValue())));
				grid.toggleNode(null, true, true);
			}
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
				grid=ns.grid, caption=[];
			value=grid.getUIValue(true);
			if (_.isArr(value)){
				_.arr.each(value,function(v){
					var item=grid.getItemByItemId(v);
					caption.push(item.caption);
				});
				ns.fireEvent("onSelect",[{value:value.join(';'),caption:caption.join(';')}]);
				ns.destroy();
			} else if(value) {
				var item=grid.getItemByItemId(value);
				if (item){
					ns.fireEvent("onSelect",[{value:value,caption:item.caption}]);
					ns.destroy();
				}
			}
			
		},
		_ctl_sbutton3_onclick:function(){
			var ns=this;
			ns.fireEvent("onCancel");
			ns.destroy(); 
		},
		_grid_ondblclick:function (profile, item, e, src){
			var ns = this, uictrl = profile.boxing(),
				value=uictrl.getUIValue(true);
			if(typeof value!="undefined"){
				if (ns.properties.type=='user'){
					if(value[0]=='u'){
						ns.fireEvent("onSelect",[{value:value,caption:item.caption}]);
						ns.destroy();
					}
				}else if(ns.properties.type=='usergroup'){
					ns.fireEvent("onSelect",[{value:value,caption:item.caption}]);
					ns.destroy();
				}
				
			}
		},
		_grid_onitemselected:function(profile,item,e,src,type){
			var ns = this, uictrl = profile.boxing();
			var str=uictrl.getUIValue();
			if(str==""){
				var value=[];
			}else{
				var value=str.split(";");
			}
//			var items=uictrl.getSubIdByItemId(item.id);
//			if(item.sub){
//				_.arr.each(item.sub,function(s){
//					uictrl.fireItemClickEvent(s.id);
//				});
//			}
			var sub=ns._get_sub_id(item);
			_.arr.each(sub,function(s){
				var i=_.arr.indexOf(value,s);
				if(i>=0&&type<0){
					value.splice(i,1);
				}else if(i<0&&type>0){
					value.push(s);
				}
			});
			str=value.join(";");
			uictrl.setUIValue(str);
		},
		_get_sub_id:function(item){
			var ns=this;
			var value=[];
			if(item.sub){
				_.arr.each(item.sub,function(s){
					value.push(s.id);
					value=value.concat(ns._get_sub_id(s));
				});
			}
			return value;
		}
	}
});
