// The default code is a com class (inherited from xui.Com)
Class('App', 'xui.Com',{
    // Ensure that all the value of "key/value pair" does not refer to external variables
    Instance:{ 
        // To initialize internal components (mostly UI controls)
        // *** If you're not a skilled, dont modify this function manually ***
        iniComponents : function(){
            // [[Code created by CrossUI RAD Tools
            var host=this, children=[], append=function(child){children.push(child.get(0));};
            
            append((new xui.UI.Block())
            .setHost(host,"ctl_block3")
            .setDock("top")
            .setHeight(50)
            );
            
            host.ctl_block3.append((new xui.UI.Image())
            .setHost(host,"ctl_image3")
            .setLeft(10)
            .setTop(10)
            .setSrc("{/}img/order.png")
            .setCustomStyle({"KEY":{"$gradients":{"stops":[{"pos":"52%", "clr":"#FFFF00"}, {"pos":"100%", "clr":"#FFFFFF", "opacity":0}], "type":"radial", "orient":"C"}, "transform":"rotate(331deg) scale(1,1) skew(0deg,0deg) translate(0px,0px)"}})
            );
            
            host.ctl_block3.append((new xui.UI.SLabel())
            .setHost(host,"ctl_slabel28")
            .setLeft(70)
            .setTop(12)
            .setCaption("Manage Orders")
            .setCustomStyle({"KEY":{"font-size":"18pt", "font-weight":"normal", "color":"#000080", "text-shadow":"0px 0px 8px #20B2AA", "$gradients":""}})
            );
            
            host.ctl_block3.append((new xui.UI.Pane())
            .setHost(host,"ctl_pane13")
            .setTop(0)
            .setWidth(538)
            .setHeight(50)
            .setRight(4)
            );
            
            host.ctl_pane13.append((new xui.UI.SLabel())
            .setHost(host,"ctl_slabel53")
            .setLeft(370)
            .setTop(27)
            .setCaption("Powered by ")
            .setCustomStyle({"KEY":{"font-style":"italic", "$gradients":""}})
            );
            
            host.ctl_pane13.append((new xui.UI.Image())
            .setHost(host,"ctl_image25")
            .setLeft(440)
            .setTop(17)
            .setSrc("http://www.crossui.com/img/logo2.png")
            .onClick("_ctl_image25_onclick")
            .setCustomStyle({"KEY":{"$gradients":"", "cursor":"pointer"}})
            );
            
            host.ctl_pane13.append((new xui.UI.SLabel())
            .setHost(host,"ctl_slabel103")
            .setLeft(190)
            .setTop(25)
            .setCaption("Theme:")
            );
            
            host.ctl_pane13.append((new xui.UI.ComboInput())
            .setHost(host,"ctl_comboinput29")
            .setDirtyMark(false)
            .setLeft(243)
            .setTop(21)
            .setWidth(90)
            .setType("listbox")
            .setItems([{"id":"default", "caption":"Default"}, {"id":"vista", "caption":"Vista"}, {"id":"moonify", "caption":"Moonify"}, {"id":"aqua", "caption":"Aqua"}])
            .setValue("vista")
            .afterValueSet("_ctl_comboinput29_aftervalueset")
            );
            
            append((new xui.UI.Block())
            .setHost(host,"blkMain")
            .setDock("fill")
            .setBorderType("none")
            );
            
            append((new xui.UI.Panel())
            .setHost(host,"ctl_panel11")
            .setDock("right")
            .setWidth(190)
            .setZIndex(1)
            .setCaption("Setup")
            .setImage("{/}img/settings.png")
            );
            
            host.ctl_panel11.append((new xui.UI.StatusButtons())
            .setHost(host,"ctl_statusbuttons1")
            .setItems([{"id":"info", "caption":"Company Information", "image":"{/}img/app.png", "imagePos":"-112px 0 "}, {"id":"shipping", "caption":"Shipping Methods", "image":"{/}img/app.png", "imagePos":"-64px 0 "}, {"id":"payment", "caption":"Payment Methods", "image":"{/}img/app.png", "imagePos":"-80px 0 "}, {"id":"employee", "caption":"Employees", "image":"{/}img/app.png", "imagePos":"-16px 0 "}, {"id":"prd", "caption":"Products", "image":"{/}img/app.png", "imagePos":"-48px 0 "}, {"id":"customer", "caption":"Customers", "image":"{/}img/app.png", "imagePos":"-32px 0 "}, {"id":"feedback", "caption":"Provide Feedback", "itemStyle":"font-weight:bold", "image":"{/}img/app.png", "imagePos":"-96px 0 "}])
            .setLeft(6)
            .setTop(10)
            .setWidth(171)
            .setHeight(250)
            .setSelMode("none")
            .setBorderType("none")
            .setItemMargin("2px 4px")
            .setItemWidth(150)
            .setItemLinker("none")
            .setValue("")
            .onClick("_ctl_statusbuttons1_onclick")
            );
            
            host.ctl_panel11.append((new xui.UI.Image())
            .setHost(host,"ctl_image24")
            .setLeft(31)
            .setTop(200)
            .setSrc("{/}img/setup.png")
            );
            
            host.ctl_panel11.append((new xui.UI.Link())
            .setHost(host,"ctl_link1")
            .setLeft(31)
            .setTop(341)
            .setWidth(140)
            .setHeight(20)
            .setCaption("Download source code")
            .onClick("_ctl_link1_onclick")
            .setCustomStyle({"KEY":{"font-style":"italic", "$gradients":""}})
            );
            
            return children;
            // ]]Code created by CrossUI RAD Tools
        },
        _ctl_statusbuttons1_onclick:function (profile,item,e,src){
            switch(item.id){
                case "info": 
                    xui.ComFactory.newCom("App.companyInfo",function(){
                        this.show();
                    });
                    break;
                case "payment": 
                    xui.ComFactory.newCom("App.paymentMethods",function(){
                        this.show();
                    });
                    break;
                case "shipping": 
                    xui.ComFactory.newCom("App.shippingMethods",function(){
                        this.show();
                    });
                    break;
                case "prd":
                    xui.ComFactory.newCom("App.products",function(){
                        this.show();
                    });
                    break;
                case "customer":
                    xui.ComFactory.newCom("App.customers",function(){
                        this.show();
                    });
                    break; 
                case "employee":
                    xui.ComFactory.newCom("App.employees",function(){
                        this.show();
                    });
                    break; 
                case "feedback":
                    xui.Dom.submit("mailTo:support@crossui.com",{subject:"Feedback on CrossUI Order Management"});
                    break;
            }
        },
        _ctl_image25_onclick:function (profile,e,src){
            xui.Dom.submit("http://www.crossui.com");
        },
        _ctl_comboinput29_aftervalueset:function (profile,oldValue,newValue){
            xui.setTheme(newValue);
        },
        _ctl_link1_onclick:function (profile, e){
            xui.Dom.submit("http://www.crossui.com/download.html");
        } ,
        iniExComs : function(com, threadid){
            var ns=this;
            xui.ComFactory.newCom("App.GridEditor", function(){
                ns.blkMain.append(this);
            }, threadid, {
                objectName:'orders',
                objectForm:'App.order'
            });
        }
    }
});