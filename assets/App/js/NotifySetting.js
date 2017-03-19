Class('App.NotifySetting', 'xui.Module',{
	autoDestroy : true,
	Instance:{
		initialize : function(){
			var ns=this;
			ns._dataFilter=null;
		},
		iniComponents : function(){
			// [[Code created by CrossUI RAD Studio
			var host=this, children=[], append=function(child){children.push(child.get(0));};
			
			append(
				xui.create("xui.DataBinder")
				.setHost(host,"databinder")
				.setName("databinder")
			);
			
			append(
				xui.create("xui.UI.Group")
				.setHost(host,"xui_ui_group1")
				.setLeft(30)
				.setTop(30)
				.setWidth(400)
				.setHeight(90)
				.setCaption("编辑提醒设置")
				.setToggleBtn(false)
			);
			
			host.xui_ui_group1.append(
				xui.create("xui.UI.CheckBox")
				.setHost(host,"xui_ui_checkbox11")
				.setDataBinder("databinder")
				.setDataField("HandEnable")
				.setLeft(7)
				.setTop(7)
				.setWidth(200)
				.setCaption("编辑部交稿、交片提前天数")
				);
			
			host.xui_ui_group1.append(
				xui.create("xui.UI.CheckBox")
				.setHost(host,"xui_ui_checkbox5")
				.setDataBinder("databinder")
				.setDataField("SubHandEnable")
				.setLeft(7)
				.setTop(37)
				.setWidth(200)
				.setCaption("分社发稿、发片提前天数")
				);
			
			host.xui_ui_group1.append(
				xui.create("xui.UI.ComboInput")
				.setHost(host,"xui_ui_input10")
				.setDataBinder("databinder")
				.setDataField("Hand")
				.setLeft(300)
				.setTop(7)
				.setWidth(60)
				.setType("number")
				.setPrecision(0)
				.setMin(0)
				.setMax(31)
				);
			
			host.xui_ui_group1.append(
				xui.create("xui.UI.ComboInput")
				.setHost(host,"xui_ui_input5")
				.setDataBinder("databinder")
				.setDataField("SubHand")
				.setLeft(300)
				.setTop(37)
				.setWidth(60)
				.setType("number")
				.setPrecision(0)
				.setMin(0)
				.setMax(31)
				);
			
			append(
				xui.create("xui.UI.Group")
				.setHost(host,"xui_ui_group2")
				.setLeft(30)
				.setTop(130)
				.setWidth(400)
				.setHeight(60)
				.setCaption("销售提醒设置")
				.setToggleBtn(false)
			);
			
			host.xui_ui_group2.append(
				xui.create("xui.UI.CheckBox")
				.setHost(host,"xui_ui_checkbox3")
				.setDataBinder("databinder")
				.setDataField("PosterOfferEnable")
				.setLeft(7)
				.setTop(7)
				.setWidth(200)
				.setCaption("邮局报数提前天数")
				);
			
			host.xui_ui_group2.append(
				xui.create("xui.UI.ComboInput")
				.setHost(host,"xui_ui_input3")
				.setDataBinder("databinder")
				.setDataField("PosterOffer")
				.setLeft(300)
				.setTop(7)
				.setWidth(60)
				.setType("number")
				.setPrecision(0)
				.setMin(0)
				.setMax(31)
				);
			
			append(
				xui.create("xui.UI.Group")
				.setHost(host,"xui_ui_group3")
				.setLeft(30)
				.setTop(210)
				.setWidth(400)
				.setHeight(60)
				.setCaption("生产提醒设置")
				.setToggleBtn(false)
			);
			
			host.xui_ui_group3.append(
				xui.create("xui.UI.CheckBox")
				.setHost(host,"xui_ui_checkbox1")
				.setDataBinder("databinder")
				.setDataField("PublishEnable")
				.setLeft(7)
				.setTop(7)
				.setWidth(200)
				.setCaption("出版提前天数")
				);
			
			host.xui_ui_group3.append(
				xui.create("xui.UI.ComboInput")
				.setHost(host,"xui_ui_input2")
				.setDataBinder("databinder")
				.setDataField("Publish")
				.setLeft(300)
				.setTop(7)
				.setWidth(60)
				.setType("number")
				.setPrecision(0)
				.setMin(0)
				.setMax(31)
				);
			
			append(
				xui.create("xui.UI.HTMLButton")
				.setHost(host,"xui_ui_htmlbutton2")
				.setLeft(30)
				.setTop(290)
				.setWidth(100)
				.setHeight(22)
				.setHtml("保存")
				.onClick("_xui_ui_htmlbutton2_onclick")
			);
			
			return children;
			// ]]Code created by CrossUI RAD Studio
		},
		events:{"onRender":"_com_onrender"},
		_com_onrender:function (com, threadid){
			var ns=this,db=ns.databinder;
			AJAX.callService('system/request',null,"notify_setting_get",null,
			function(rsp){
				db.setData(rsp.data);
				db.updateDataToUI();
			},function(){
			},function(result){
			});
		},
		_xui_ui_htmlbutton2_onclick:function (profile,e,value){
			var ns = this, uictrl = profile.boxing(),db=ns.databinder;
			db.updateDataFromUI();
			var data=db.getUIValue();
			AJAX.callService('system/request',null,"notify_setting_set",data,
			function(rsp){
			},function(){
			},function(result){
			});
		}
	}
});
