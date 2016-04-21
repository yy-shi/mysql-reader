$(document).ready(function(){
    $('#index-form').submit(function(){
        var $this = $(this);
        var sql = $this.find('textarea[name=sql]').val();
        getQueryData($this, sql);
        return false;
    });
    $('#host-selector').change(function(){
        var host = $(this).find('select[name=host]').val();
        $.post('/getdb',{host:host},function(json){
            if(json.code==403){
                alert(json.msg);
                return  window.location.reload();
            }
            $('#db-selector').html('');
            $.each(json.data,function(index,obj){
                $('#db-selector').append('<option value='+obj.Database+'>'+obj.Database+'</option>');
            })
        },'json');   
    });;
    $('.action-btns button').click(function(){
        var sql = $(this).attr('sql');
        var tform = $('#index-form');
        if(sql.match(/%s/)){
            var inputSql = tform.find('textarea[name=sql]').val();
            if(inputSql){
                sql = sql.replace("%s", inputSql);
            }else{
                alert('请输入你的查询语句');
                return false;
            }
        }
        getQueryData(tform, sql);
    });
});
function renderQueryResult(data){
    var thead = $('.result-main thead');
    var tbody = $('.result-main tbody');
    var renderHead = true;
    tbody.empty();
    $.each(data,function(index,val){
        if(renderHead){
            var headStr="<tr><th>#</th>";
            $.each(val,function(key){
                headStr += "<th>"+key+"</th>";
            });
            headStr+="</tr>";
            thead.html(headStr);
            renderHead = false;
        }
        var bodyStr="<tr><th>"+(index+1)+"</th>";
        $.each(val,function(key2,val2){
            bodyStr += "<th>"+val2+"</th>";
        });
        bodyStr+="</tr>";
        tbody.append(bodyStr); 
    });
}

function getQueryData($this,sql){
    var host = $this.find('select[name=host]').val();
    var dbName = $this.find('select[name=dbname]').val();
    if($this.attr('sending')){
        return false;
    }
    $this.attr('sending',1);
    $.post('/query',{query:sql,host:host,dbname:dbName},function(data){
        if(data.code==403){
            alert(data.msg);
          return  window.location.reload();
        }
        $('.result-box').show();
        $('pre.result-sql').find('code').html(sql);
        if(data.code===200){
            $('p.result-title').removeClass('alert-warning').addClass('alert-success');
            $('p.result-title').html('执行成功,结果集行数('+data.data.length+')');
                renderQueryResult(data.data);
                }else{
                    $('p.result-title').removeClass('alert-success').addClass('alert-warning');
                    $('p.result-title').html(data.msg);
                    $('p.result-main').html('');
                }
                hljs.highlightBlock( $('pre.result-sql').find('code').get(0));
                $this.removeAttr('sending');
                },'json');
            }

