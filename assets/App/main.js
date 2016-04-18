// 默认的代码是一个从 xui.Com 派生来的类
Class('App', 'xui.Com',{
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
                    "min" : 60,
                    "size" : 60,
                    "locked" : true,
                    "folded" : false,
                    "hidden" : false,
                    "cmd" : false
                },{
                    "id" : "main"
                }])
            );
            
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
                    "caption" : "main",
                }])
                .setValue("main")
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
                    xui.ComFactory.newCom(item.app,function(){
                    	(new xui.UI.Div())
                    	.setDock("fill")
                    	.append(this)
                    	.show(tabs,id);
                    },null,{target:item.target});
                    tabs.fireItemClickEvent(item.id);
                }
            } else{
            	tabs.fireItemClickEvent(item.id);
            }
            profile.boxing().setUIValue("");
        }
    }
});

xui.launch('App',null,'cn','vista');
