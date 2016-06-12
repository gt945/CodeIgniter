// 默认的代码是一个从 xui.Com 派生来的类
Class('App.GridFilter', 'xui.Com',{
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
            
            append(
                (new xui.UI.Dialog())
                .setHost(host,"dialog")
                .setLeft(130)
                .setTop(140)
                .setWidth(640)
                .setHeight(320)
                .setCaption("过滤")
                .setMinBtn(false)
                .setMaxBtn(false)
                .setRestoreBtn(false)
                .setOverflow("overflow-x:hidden;overflow-y:auto")
            );
            
            return children;
            // ]]Code created by CrossUI RAD Studio
        },
        // 可以自定义哪些界面控件将会被加到父容器中
        customAppend : function(parent, subId, left, top){
        	this.dialog.showModal(parent, left, top);
        	grp = this._append_group(this.dialog);
        	this._append_group(grp);
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
        _append_group:function(into){
        	var grp=(new xui.UI.Group())
        	.setDock("width")
//        	.setPosition("relative")
        	.setOverflow("overflow-x:hidden;overflow-y:auto")
        	.setDockMargin({
                "left" : 10,
                "top" : 30,
                "right" : 10,
                "bottom" : 10
            })
            .setHeight("auto")
            .setToggleBtn(false)
        	.append(
        			(new xui.UI.Pane())
//        			.setPosition("relative")
        			.setDock("width")
        			.setHeight("auto")
        			.append(
        					(new xui.UI.Button())
        					.setLeft(0)
        					.setCaption("add rule")
        			)
        			.append(
        					(new xui.UI.Button())
        					.setLeft(130)
        					.setCaption("add group")
        			)
        			.setOverflow("overflow-x:hidden;overflow-y:auto")
			)
			.append(
					(new xui.UI.Pane())
//					.setPosition("relative")
					.setDock("width")
					.setHtml(_())
					.setHeight("auto")
			);
        	into.append(grp);
        	return grp;
        }
    }
});