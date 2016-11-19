// 默认的代码是一个从 xui.Com 派生来的类
Class('App.login', 'xui.Com',{
    // 要确保键值对的值不能包含外部引用
    Instance:{
        // 本Com是否随着第一个控件的销毁而销毁
        autoDestroy : true,
        // 初始化属性
        properties : {},
        // 实例的属性要在此函数中初始化，不要直接放在Instance下
        initialize : function(){
        	this.encrypt=new JSEncrypt();
        },
        // 初始化内部控件（通过界面编辑器生成的代码，大部分是界面控件）
        // *** 如果您不是非常熟悉XUI框架，请慎重手工改变本函数的代码 ***
        iniComponents : function(){
            // [[Code created by CrossUI RAD Studio
            var host=this, children=[], append=function(child){children.push(child.get(0));};
            
            append(
            	(new xui.DataBinder())
                .setHost(host,"databinder")
                .setName("databinder")
                .setDataSourceType("remoting")
                .setQueryMethod("POST")
                .setRequestType("HTTP")
                .setResponseType("JSON")
                //.setQueryURL(SITEURL+'user/login')
            );
            
            append(
                (new xui.UI.Dialog())
                .setHost(host,"dialog")
                .setResizer(false)
                .setCaption("登录")
                .setMovable(false)
                .setMinBtn(false)
                .setMaxBtn(false)
                .setCloseBtn(false)
            );
            
            host.dialog.append(
                (new xui.UI.ComboInput())
                .setHost(host,"username")
                .setDataBinder("databinder")
                .setDataField("username")
                .setLeft(35)
                .setTop(30)
                .setWidth(200)
                .setType("input")
                .setLabelSize(80)
                .setLabelCaption("用户名　")
                .setShowDirtyMark(false)
                );
            
            host.dialog.append(
                (new xui.UI.ComboInput())
                .setHost(host,"password")
                .setDataBinder("databinder")
                .setDataField("password")
                .setLeft(35)
                .setTop(70)
                .setWidth(200)
                .setType("password")
                .setLabelSize(80)
                .setLabelCaption("密　码　")
                .setShowDirtyMark(false)
                );
            
            host.dialog.append(
                (new xui.UI.ComboInput())
                .setHost(host,"captcha")
                .setDataBinder("databinder")
                .setDataField("captcha")
                .setLeft(35)
                .setTop(110)
                .setWidth(200)
                .setType("input")
                .setLabelSize(80)
                .setLabelCaption("验证码　")
                .setShowDirtyMark(false)
                );
            
            host.dialog.append(
            	(new xui.UI.Pane())
            	.setHost(host,"captcha_block")
            	.setLeft(115)
                .setTop(150)
                .setWidth(120)
                .setHeight(50)
            	);
            
            host.captcha_block.append(
	            (new xui.UI.Image())
	            .setHost(host,"captcha_image")
	            .setDock("fill")
	            .setTips("点击刷新验证码")
                .onRender("_update_captcha_image")
                .onClick("_update_captcha_image")
	            );

            host.dialog.append(
                (new xui.UI.Button())
                .setHost(host)
                .setLeft(100)
                .setTop(225)
                .setWidth(100)
                .setCaption("登　录")
                .onClick("_login_onclick")
                );
            
            return children;
            // ]]Code created by CrossUI RAD Studio
        },
        events:{"onRender":"_com_onrender"},
        // 可以自定义哪些界面控件将会被加到父容器中
        customAppend : function(parent, subId, left, top){
        	this.dialog.showModal(parent, left, top);
            return false;
        },
        // 加载其他资源可以用本函数
        iniResource: function(com, threadid){
            //xui.Thread.suspend(threadid);
            //var callback=function(resp){
            //    com.encrypt.setPrivateKey(resp.data);
            //    xui.Thread.resume(threadid);
            //};
            //xui.Thread.observableRun(function(threadid){
            //    xui.Ajax(SITEURL+'user/pubkey', null, callback, null,threadid).start();
            //});
        },
        // 加载其他Com可以用本函数
        iniExComs : function(com, threadid){
            //xui.Thread.suspend(threadid);
            //var callback=function(/**/){
            //    /**/
            //    xui.Thread.resume(threadid);
            //};
        },
        _com_onrender:function(){
        	var ns=this;
            xui.Event.keyboardHook("enter", 0, 0, 0, function(){
            	var db = ns.databinder.updateDataFromUI(true);
            	if (db.getData("username").value.length 
            			&&db.getData("password").value.length
            			&&db.getData("captcha").value.length){
            		ns._login_onclick();
            	}
            });
        },
        _login_onclick:function(){
            var ns = this;
            var db = ns.databinder.updateDataFromUI(true);
            var ps=db.getData("password").value;
            vp=md5(md5(ps),db.getData("captcha").value,false);
            ve=ns.encrypt.encrypt(vp);
            
            args={
             		username:db.getData("username").value,
            		password:ve,
            		captcha:db.getData("captcha").value,
            		captcha_time:ns.captcha_time
    	    };
            ns.databinder.setQueryArgs(args);
            if(args.username.length&&args.password.length&&args.captcha) {
            	ns.databinder.invoke(
            			function(rsp){
            				if (rsp.ok){
            					window.location.replace(rsp.url);
            				} else {
            					xui.alert(rsp.error);
            					ns._update_captcha_image();
            				}
            			},
            			function(){
            				
            			},
            			function(){
            				ns.dialog.busy(true);
            			},
            			function(){
            				ns.dialog.free();
            			}
            	);
            }else{
            	xui.alert("请填写完整");
            }
        },
        _update_captcha_image:function(){
            var ns = this;
            ns.captcha_block.busy(null,null);
            xui.request(SITEURL+'user/captcha', null, function(rsp){
        		ns.captcha_image.setSrc(rsp.url);
        		ns.captcha_time=rsp.time;
        		ns.pubkey=rsp.pubkey;
        		ns.encrypt.setPrivateKey(rsp.pubkey);
            	ns.captcha_block.free();
            });
        }
    }
});

xui.launch('App.login',function(){xui('loading').remove();},'cn','vista');
