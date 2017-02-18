// 默认的代码是一个从 xui.Module 派生来的类
Class('App.UserInfo', 'xui.Module',{
	autoDestroy : true,
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
			);

			append(
				(new xui.APICaller())
				.setHost(host,"apicaller")
				.setName("apicaller")
				.setQueryMethod("POST")
				.setRequestType("FORM")
				.setResponseType("JSON")
				.setQueryURL(SITEURL+'user/updateinfo')
			);

			append(
				(new xui.UI.Dialog())
				.setHost(host,"dialog")
				.setLeft(180)
				.setTop(120)
				.setWidth(320)
				.setHeight(310)
				.setCaption("用户信息")
				.setResizer(false)
				.setMovable(false)
				.setMinBtn(false)
				.setMaxBtn(false)
			);
			
			host.dialog.append(
				(new xui.UI.ComboInput())
				.setHost(host,"password")
				.setDataBinder("databinder")
				.setDataField("password")
				.setShowDirtyMark(false)
				.setLeft(35)
				.setTop(30)
				.setWidth(200)
				.setLabelSize(80)
				.setLabelCaption("当前密码")
				.setType("password")
				.setTips("当前使用的密码,必须填写")
				.setCaption("")
				.setCustomStyle({
					"LABEL" : {
						"text-align" : "center"
					}
				})
				);
			
			host.dialog.append(
				(new xui.UI.ComboInput())
				.setHost(host,"password1")
				.setDataBinder("databinder")
				.setDataField("password1")
				.setShowDirtyMark(false)
				.setLeft(35)
				.setTop(70)
				.setWidth(200)
				.setLabelSize(80)
				.setLabelCaption("新密码")
				.setType("password")
				.setTips("设置新密码,不修改请留空")
				.setCaption("")
				.setCustomStyle({
					"LABEL" : {
						"text-align" : "center"
					}
				})
				);
			
			host.dialog.append(
				(new xui.UI.ComboInput())
				.setHost(host,"password2")
				.setDataBinder("databinder")
				.setDataField("password2")
				.setShowDirtyMark(false)
				.setLeft(35)
				.setTop(110)
				.setWidth(200)
				.setLabelSize(80)
				.setLabelCaption("重复密码")
				.setType("password")
				.setTips("设置新密码,不修改请留空")
				.setCaption("")
				.setCustomStyle({
					"LABEL" : {
						"text-align" : "center"
					}
				})
				);
			
			host.dialog.append(
				(new xui.UI.ComboInput())
				.setHost(host,"name")
				.setDataBinder("databinder")
				.setDataField("name")
				.setShowDirtyMark(false)
				.setLeft(35)
				.setTop(150)
				.setWidth(200)
				.setLabelSize(80)
				.setLabelCaption("姓名")
				.setType("input")
				.setCaption("")
				.setCustomStyle({
					"LABEL" : {
						"text-align" : "center"
					}
				})
				);
			
			host.dialog.append(
				(new xui.UI.ComboInput())
				.setHost(host,"contact")
				.setDataBinder("databinder")
				.setDataField("contact")
				.setShowDirtyMark(false)
				.setLeft(35)
				.setTop(190)
				.setWidth(200)
				.setLabelSize(80)
				.setLabelCaption("联系方式")
				.setType("input")
				.setCaption("")
				.setCustomStyle({
					"LABEL" : {
						"text-align" : "center"
					}
				})
				);
			
			host.dialog.append(
				(new xui.UI.Button())
				.setHost(host,"save")
				.setLeft(50)
				.setTop(235)
				.setWidth(100)
				.setCaption("确定")
				.onClick("_save_onclick")
				);
			
			host.dialog.append(
				(new xui.UI.Button())
				.setHost(host,"close")
				.setLeft(170)
				.setTop(235)
				.setWidth(100)
				.setCaption("关闭")
				.onClick("_close_click")
				);
			
			return children;
			// ]]Code created by CrossUI RAD Studio
		},
		// 可以自定义哪些界面控件将会被加到父容器中
		customAppend : function(parent, subId, left, top){
			this.dialog.showModal(parent, left, top);
			return true;
		},
		// 加载其他资源可以用本函数
		iniResource: function(com, threadid){
			var ns=this;
			xui.Thread.suspend(threadid);
			var callback=function(){
				xui.Thread.resume(threadid);
			};
			xui.request(SITEURL+'user/userinfo', null, function(rsp){
				ns.pubkey=rsp.pubkey;
				ns.encrypt.setPrivateKey(rsp.pubkey);
				ns.name.setValue(rsp.name);
				ns.contact.setValue(rsp.contact);
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
		_close_click:function(){
			this.dialog.close();
		},
		_save_onclick:function(){
			var ns = this;
			var db = ns.databinder;
			db.updateDataFromUI(true);
			var ps=db.getData("password");
			var ps1=db.getData("password1");
			var ps2=db.getData("password2");
			var vp="";
			if(ps.length==0){
				xui.alert("请输入密码");
				return true;
			}
			if(ps1.length>0||ps2.length>0){
				if(ps1!=ps2){
					xui.alert("新密码不相符");
					return true;
				}
				if(ps1.length<8){
					xui.alert("新密码至少8位");
					return true;
				}
				vp=ns.encrypt.encrypt(md5(ps1));
			}
			var ve=ns.encrypt.encrypt(md5(ps));
			var args={
			 		name:db.getData("name"),
					password:ve,
					newpassword:vp,
					contact:db.getData("contact")
			};
			ns.apicaller.setQueryArgs(args);
			ns.apicaller.invoke(
					function(rsp){
						if (rsp.ok){
							xui.message(rsp.error);
							ns.dialog.free();
							ns.dialog.close();
						} else {
							xui.alert(rsp.error);
						}
					},
					function(){
						
					},
					function(){
						ns.dialog.busy(true);
					},
					function(){
						if (!ns.isDestroyed()){
							ns.dialog.free();
						}
					}
			);
		}
	}
});