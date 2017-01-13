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
                .setItems([{
                    "id" : "grp1",
                    "sub" : [{
                        "id" : "message",
                        "image" : "@xui_ini.appPath@image/message.png",
                        "caption" : "消息"
                    },{
                        "id" : "logout",
                        "image" : "@xui_ini.appPath@image/logout.png",
                        "caption" : "退出"
                    },{
                        "id" : "userinfo",
                        "image" : "@xui_ini.appPath@image/user.png",
                        "caption" : USERNAME
                    }],
                    "caption" : "grp1"
                }])
                .onClick("_toolbar_click")
                , "before");
            
            
            host.layout_v.append(
                (new xui.UI.Layout())
                .setHost(host,"layout_h")
                .setItems([{
                    "id" : "before",
                    "pos" : "before",
                    "min" : 150,
                    "size" : 150,
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
            
            host.layout_h.append( _.unserialize(MENUS), "before");
            
            host.layout_h.append(
                (new xui.UI.Tabs())
                .setHost(host,"main_tabs")
                .setItems([{
                    "id" : "main",
                    "caption" : "main"
                }])
                .setValue("main")
                .beforePageClose("_xui_ui_main_tabs_beforepageclose")
                .onIniPanelView("_tabs_oninipanelview")
                , "main");
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
            //xui.Thread.suspend(threadid);
            //var callback=function(/**/){
            //    /**/
            //    xui.Thread.resume(threadid);
            //};
        },
        // 加载其他Com可以用本函数
        iniExComs : function(com, threadid){
            //xui.Thread.suspend(threadid);
            //var callback=function(/**/){
            //    /**/
            //    xui.Thread.resume(threadid);
            //};
        },
        _menus_selected: function(profile, item, src) {
        	var tabs=SPA.main_tabs,id=item.id;
            if(!tabs.getItemByItemId(id)){
                if(!tabs.isDestroyed()){
                	tabs.insertItems([{id:id,caption:item.caption,closeBtn:true}], null, false);
                    xui.ModuleFactory.newCom(item.app,function(){
                    	(new xui.UI.Div())
                    	.setDock("fill")
                    	.append(this)
                    	.show(tabs,id);
                    },null,{target:item.target,id:item.id});
                    tabs.fireItemClickEvent(item.id);
                }
            } else{
            	tabs.fireItemClickEvent(item.id);
            }
            profile.boxing().setUIValue("");
        },
        _toolbar_click:function(profile, item, group, e, src){
        	 var ns = this;
             switch(item.id){
             case "logout":
            	 xui.confirm("确认", "确定退出吗?", function(){
            		 window.location.replace(SITEURL+'user/logout');
                 });
            	 break;
             case "userinfo":
            	 xui.ModuleFactory.newCom("App.UserInfo",function(){
            		 this.show();
            	 },null);
            	 break;
             case "message":
                 xui.ModuleFactory.newCom("App.Message",function(){
            		 this.show();
            	 },null);
                 break;
             }
        },
        _xui_ui_main_tabs_beforepageclose:function (profile,item,src){
            var ns = this, uictrl = profile.boxing();
            //xui.confirm("确认", "确定关闭吗?", function(){
            	uictrl.removeItems(item.id);
            //});
            return false;
        },
        _tabs_oninipanelview:function(profile, item){
            var tabs=SPA.main_tabs;
            var id=item.id;
            if (id=="main"){
                xui.ModuleFactory.newCom("App.Dashboard",function(){
                    (new xui.UI.Div())
                        .setDock("fill")
                        .append(this)
                        .show(tabs,id);
                });
            }
        }
    }
});

xui.launch('App.main',function(){xui('loading').hide();},'cn','vista');
