$(document).ready(function(){
 renderHistory();  
$('ul.nav-tabs li').click(function(){
    $(this).siblings().removeClass('active');
    $(this).addClass('active');
    renderHistory();  
});
$('.history-box').on('click','li',function(){
    var index = $('ul.nav-tabs li.active').attr('role');
    if(index=="history"){
        var sql  = $(this).html();
    }else{
       var sqlHistory = getLocalStorage();
       var sql = sqlHistory.save[$(this).html()];
    }

    $('#index-form').find('textarea[name=query]').val(sql);
});
$('#save_sql_btn').click(function(){
 var name;
 if(name=prompt("请输入sql保存的名字")){
    var sql = $('#index-form').find('textarea[name=query]').val();
    var sqlHistory = getLocalStorage();
    sqlHistory.save[name] = sql;
    localStorage.sqlHistory=JSON.stringify(sqlHistory);
    $('ul.nav-tabs li[role=save]').trigger('click');
 }
});
$('#query_btn').click(function(){
        var $form = $('#index-form');
        var sql = $form.find('textarea[name=query]').val();
        if(sql){
            getQueryData($form, sql);
        }    
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
    $('#export_csv_btn').click(function(){
        var tform = $('#index-form');
        tform.find("input[name=export_csv]").val(1);
        var sql = tform.find('textarea[name=query]').val();
        if(sql){
            tform.submit();
        }
    });
    $('.action-btns button').click(function(){
        var sql = $(this).attr('sql');
        var tform = $('#index-form');
        if(sql.match(/%s/)){
            var inputSql = tform.find('textarea[name=query]').val();
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
    var inputSql = $this.find('textarea[name=query]').val();
    $this.attr('sending',1);
    $.post('/query',{query:sql,host:host,dbname:dbName},function(data){
        if(data.code==403){
            alert(data.msg);
          return  window.location.reload();
        }
        $('.result-box').show();
        $('pre.result-sql').find('code').html(sql);
        if(data.code===200){
        var sqlHistory = getLocalStorage();
            if(inputSql){
                sqlHistory.history=uniqueAndPush(sqlHistory.history,inputSql);
            }
            localStorage.sqlHistory=JSON.stringify(sqlHistory);
            $('ul.nav-tabs li[role=history]').trigger('click');
            $('p.result-title').removeClass('alert-warning').addClass('alert-success');
            $('p.result-title').html('执行成功,结果集行数('+data.data.length+')');
                renderQueryResult(data.data);
                }else{
                    $('p.result-title').removeClass('alert-success').addClass('alert-warning');
                    $('p.result-title').html(data.msg);
                    $('p.result-main').html('');
                }
                hljs.highlightBlock( $('pre.result-sql').find('code').get(0));
                },'json').always(function() {
                $this.removeAttr('sending');
            });
     ;
            }

function renderHistory(){
    var index = $('ul.nav-tabs li.active').attr('role');
    var sqlHistory = getLocalStorage();
    var ul = $('#sql-group-ul');
    ul.empty();
    $.each(sqlHistory[index],function(i,v){
        ul.prepend('<li class="list-group-item">'+(isNaN(i)?i:v)+'</li>');
    });
}
function uniqueAndPush(arr,v){
    arr.sort();
    v= v.replace(/(^\s*)|(\s*$)/g, "");
    var re=[];
    for(var i = 0; i < arr.length; i++)
    {
        if(arr[i]==null){
            continue;
        }
        if( arr[i] !== re[re.length-1] && arr[i] !==v)
        {
            re.push(arr[i]);
        }
    }
    if(v){
        re.push(v);
    }
    return re.slice(0,99);
}

function getLocalStorage(){
   if(typeof localStorage.sqlHistory =="undefined"){
        var sqlHistory = {history:[],save:{}};
    }else{
        var sqlHistory = JSON.parse(localStorage.sqlHistory);
    }
  return sqlHistory; 
}

