AJAX={
    serviceURI:SITEURL+'xui/request',
    pageSize:20,
    callService:function(objectName, action, paras, callback, onStart, onEnd, file){
        _.tryF(onStart);
        xui.Thread.observableRun(function(threadid){
            paras=paras||{};
            _.merge(paras,{action:action});
            
            var data={key:objectName, paras:paras}, options;
            options={method:'post'};
                      
            if(file){
                data.file=file;
            }
            xui.request(AJAX.serviceURI, data, function(rsp){
                var obj=rsp,result="ok";
                if(obj){
                    if(obj.code==200){
                        if(obj.data && obj.data.warn){
                            result="fail";
                            xui.message(obj.data.warn.message || obj.data.warn);
                        }else{
                            _.tryF(callback,[obj]);
                        }
                    }else{
                        result="fail";
                        xui.alert(obj.msg);
                    }
                }else{
                    result="fail";
                    xui.alert(_.serialize(rsp));
                }
                _.tryF(onEnd,[result]);
            },function(rsp){
                xui.alert(_.serialize(rsp));
                _.tryF(onEnd,["fail"]);
            }, threadid,options);
        });
    }
};