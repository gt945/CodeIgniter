// 默认的代码是一个从 xui.Module 派生来的类
Class('App.main', 'xui.Module',{
	// 要确保键值对的值不能包含外部引用
	Instance:{
		// 本Com是否随着第一个控件的销毁而销毁
		autoDestroy : true,
		// 初始化属性
		properties : {},
		// 实例的属性要在此函数中初始化，不要直接放在Instance下
		initialize : function(){
		},
		// 初始化内部控件（通过界面编辑器生成的代码，大部分是界面控件）
		// *** 如果您不是非常熟悉XUI框架，请慎重手工改变本函数的代码 ***
		iniComponents : function(){
			// [[Code created by CrossUI RAD Studio
			var host=this, children=[], append=function(child){children.push(child.get(0));};
			SPA=this;
			append(
				(new xui.UI.Layout())
				.setHost(host,"layout_v")
				.setItems([{
					"id" : "before",
					"pos" : "before",
					"min" : 32,
					"size" : 32,
					"locked" : true,
					"folded" : false,
					"hidden" : false,
					"cmd" : false
				},{
					"id" : "main"
				}])
			);
			
			host.layout_v.append(
				(new xui.UI.ToolBar())
				.setHost(host,"toolbar")
				.setHAlign("right")
				.setHandler(false)
				.setDock("fill")
				.onClick("_toolbar_click")
				, "before");

			host.layout_v.append(
				(new xui.UI.Layout())
				.setHost(host,"layout_h")
				.setItems([{
					"id" : "before",
					"pos" : "before",
					"min" : 165,
					"size" : 165,
					"locked" : false,
					"folded" : false,
					"hidden" : false,
					"cmd" : true
				},{
					"id" : "main",
					"min" : 500
				}])
				.setType("horizontal")
				, "main");
			var menus=_.unserialize(MENUS);
			menus.setHost(host,'menus');
			host.layout_h.append( menus, "before");
			
			host.layout_h.append(
				(new xui.UI.Tabs())
				.setHost(host,"main_tabs")
				.setItems([{
					"id" : "messages",
					"caption" : "任务消息",
					"app" : "App.Messages",
					"target" : "messages"
				}])
				.setValue("messages")
				.beforePageClose("_xui_ui_main_tabs_beforepageclose")
				.onIniPanelView("_tabs_oninipanelview")
				.onContextmenu("_tabs_oncontextmenu")
				, "main");
			
			append(
				(new xui.UI.PopMenu)
				.setHost(host,"tabs_popmenu")
				.setWidth(82)
				.setHeight(80)
			);
			return children;
			// ]]Code created by CrossUI RAD Studio
		},
		// 可以自定义哪些界面控件将会被加到父容器中
		customAppend : function(parent, subId, left, top){
			// "return false" 表示默认情况下所有的第一层内部界面控件会被加入到父容器
			return false;
		},
		// 加载其他资源可以用本函数
		iniResource: function(com, threadid){
			var ns=this;
			xui.Thread.suspend(threadid);
			var callback=function(){
				xui.Thread.resume(threadid);
			};
			AJAX.callService('system/request', null, "toolbar", null, function(rsp){
				ns.toolbar.setItems(rsp.data.toolbar);
				ns.properties.settings=rsp.data.settings;
				for(var i=0;i<10;i++){
					xui.Event.keyboardHook(String(i),0,0,1,function(key){
						var shortkey=ns.properties.settings.shortkey;
						var target=shortkey['key'+key];
						if(target){
							var submenus=ns.menus.getChildren();
							_.arr.each(submenus,function(m){
								var item=m.getItemByItemId(target);
								if (item){
									m.boxing().fireItemClickEvent(target);
									return false;
								}
							});
						}
					},[i]);
				}
				callback();
			});
		},
		// 加载其他Com可以用本函数
		iniExComs : function(com, threadid){
			//xui.Thread.suspend(threadid);
			//var callback=function(/**/){
			//	/**/
			//	xui.Thread.resume(threadid);
			//};
		},
		_load_tab:function(item){
			var tabs=SPA.main_tabs;
			xui.ModuleFactory.newCom(item.app,function(){
				(new xui.UI.Div())
				.setDock("fill")
				.append(this)
				.show(tabs,item.id);
			},null,{target:item.target,id:item.id});
			_.each(SPA.properties.settings.shortkey, function(id,index){
				if(id==item.id){
					var key=parseInt(index.substr(3));
					tabs.updateItem(item.id,{caption:'['+key+']'+item.caption,_caption:item.caption,_key:key});
				}
			});
		},
		_menus_selected: function(profile, item, src) {
			var tabs=SPA.main_tabs,id=item.id;
			if(!tabs.getItemByItemId(id)){
				if(!tabs.isDestroyed()){
					tabs.insertItems([{id:id,caption:item.caption,closeBtn:true}], null, false);
					this._load_tab(item);
					tabs.fireItemClickEvent(item.id);
				}
			} else{
				tabs.fireItemClickEvent(item.id);
			}
			profile.boxing().setUIValue("");
		},
		_toolbar_click:function(profile, item, group, e, src){
			var ns=this;
			switch(item.id){
			case "logout":
				xui.confirm("确认", "确定退出吗?", function(){
					window.location.replace(SITEURL+'user/logout');
				});
				break;
			case "userinfo":
				xui.ModuleFactory.newCom("App.UserInfo",function(){
					if (!_.isEmpty(this)){
						this.show();
					}
				},null);
				break;
			case "setting":
				xui.ModuleFactory.newCom("App.Setting",function(){
					if (!_.isEmpty(this)){
						this.show();
					}
				},null);
				break;
			case "switch_to":
				xui.ModuleFactory.newCom("App.Switch",function(){
					if (!_.isEmpty(this)){
						this.show();
					}
				},null);
				break;
			case "switch_back":
				AJAX.callService('system/request', null, "user_switch_back", null, function(rsp){
					location.reload();
				});
				break;
			}
		},
		_xui_ui_main_tabs_beforepageclose:function (profile,item,src){
			var ns=this, uictrl=profile.boxing();
			//xui.confirm("确认", "确定关闭吗?", function(){
				uictrl.removeItems(item.id);
			//});
			return false;
		},
		_tabs_oninipanelview:function(profile, item){
			var tabs=SPA.main_tabs;
			var id=item.id;
			if (id=="messages"){
				SPA._load_tab(item);
			}
		},
		_tabs_oncontextmenu:function(profile,e,src,item){
			var ns=this,tabs=ns.main_tabs;
			var target=e.target||e.srcElement;
			var tab=tabs.getItemByDom(target.id);
			if (tab) {
				ns.tabs_popmenu.setItems([{
					"id" : "close_all",
					"caption" : "全部关闭"
				},
				{
					"id" : "close_others",
					"caption" : "关闭其他"
				},
				{
					"id" : "short_key",
					"caption" : tab._key >= 0 ? "清除快捷键":"设置快捷键"
				}])
				.pop(target);
				ns.tabs_popmenu.onMenuSelected(function(profile,item,src){
					var items=tabs.getItems();
					switch(item.id){
						case "close_all":
							var r=[];
							_.arr.each(items,function(i){
								if(i.closeBtn){
									r.push(i.id);
								}
							});
							tabs.removeItems(r);
							break;
						case "close_others":
							var r=[];
							_.arr.each(items,function(i){
								if(i.closeBtn&&i.id!=tab.id){
									r.push(i.id);
								}
							});
							tabs.removeItems(r);
							break;
						case "short_key":
							if(tab.closeBtn){
								if(tab._key>=0){
									ns._setup_shortkey(-1,tab);
								}else{
									xui.ModuleFactory.newCom("App.Input",function(){
										this.show();
									},null,{caption:"输入一个0～9的数字",label:"设置之后可以通过Alt+数字快速切换到标签页"},
									{
										onSelect:function(key){
											var num=parseInt(key);
											if(num>=0&&num<10){
												ns._setup_shortkey(num,tab);
											}
										}
									});
								}
							}
							break;
					}
				});
				return false;
			}
			return true;
			
		},
		_setup_shortkey:function(num,tab){
			var ns=this,settings=ns.properties.settings,tabs=ns.main_tabs;
			var key='key'+(num>=0?num:tab._key);
			var old=tab._caption ? tab._caption : tab.caption;
			var caption=old;
			if(!settings.shortkey){
				settings.shortkey={};
			}
			if(num>=0){
				if(settings.shortkey[key]){
					xui.alert('该快捷键已被占用');
					return false;
				}else{
					settings.shortkey[key] = tab.id;
					caption = '['+num+']'+old;
				}
			}else{
				delete settings.shortkey[key];
			}
			AJAX.callService('system/request', null, "update_shortkey", {settings:ns.properties.settings}, function(rsp){
				tabs.updateItem(tab.id,{caption:caption,_caption:old,_key:num});
			});
			return true;
			
		}
		
	}
});

xui.launch('App.main',function(){xui('loading').hide();},'cn','vista');
