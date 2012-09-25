var refreshId;

function stop_refresh()
{
    clearInterval(refreshId);
    refreshId=null;
    $("#start_ar").removeAttr('disabled');
    $("#stop_ar").attr('disabled','disabled');
}

function start_refresh()
{
 
    refreshId = setInterval(function()
    {
        refresh_output();
    }, $('#auto_time').val());
    
    $("#start_ar").attr('disabled','disabled');
    $("#stop_ar").removeAttr('disabled');
}

function init() 
{
    $("#loading").ajaxStart(function(){$(this).show();}).ajaxStop(function(){$(this).hide();});
    refresh_radio();
    refresh_interfaces();
    refresh_monitors();
    clear_all();
}

function clear_all()
{
    clear_output();
    $("#victime").val("");
    $("#ap").val("");
    $("#log").val("");
    $('#button_start').attr('disabled',"disabled");
    $('#button_stop').attr("disabled",'disabled');
    $('#button_refresh').attr('disabled',"disabled");
    $('#button_clear').attr('disabled',"disabled");
}

function clear_output()
{
    $("#output").val("");
}

function refresh_radio() 
{
    $('#list_radio').load('reaver_actions.php?list&radio');
}
function refresh_interfaces() 
{
    $('#list_int').load('reaver_actions.php?list&int');
}

function refresh_monitors() 
{
    $('#list_mon').load('reaver_actions.php?list&mon');
}


function refresh_available_ap() 
{
    
    $.ajax({
        type: "GET",
        data: "available_ap&interface="+$("#interfaces").val(),
        url: "reaver_actions.php",
        success: function(msg){
            $("#list_ap").html(msg);
            
            $('#survey-grid tr').click(function() 
            { 
                selectVictime($(this).attr("name"));
            });
           
           			   
				
        }
    });
}


function selectVictime(victime) 
{
    var arr  = victime.split(',');
    var ap=arr[0];
    var bssid=arr[1];
    $("#ap").val(ap);
    $("#victime").val(bssid);
    $('#button_start').removeAttr("disabled");
    
}

function refresh_output() 
{

    $.ajax({
        type: "GET",
        data: "reaver=1&refresh=1&bssid="+$("#victime").val(),
        url: "reaver_actions.php",
        success: function(msg){
            var psconsole = $('#output');
            if($("#output").val()!=msg)
            {
                $("#output").val(msg).scrollTop(psconsole[0].scrollHeight - psconsole.height());	
            }
        }
    });

}


function start_attack() 
{
    var inter = $('#mon').val();
    var v = $("#victime").val();
    var option_S=$("#option_S").attr('checked');
    var option_a=$("#option_a").attr('checked');
    $.ajax({
        type: "GET",
        data: "reaver=1&start=1&interface="+inter+"&victime="+v+"&S="+option_S+"&a="+option_a,
        url: "reaver_actions.php",
        success: function(msg){
            $('#button_start').attr('disabled',"disabled");
            $('#button_stop').removeAttr("disabled");
           
            $('#button_refresh').removeAttr("disabled");
            $('#button_clear').removeAttr('disabled');
            
            append_log(msg);
            refresh_output();
        }
    });
	
	
}

function stop_attack() 
{
    

    $.ajax({
        type: "GET",
        data: "reaver=1&stop=1",
        url: "reaver_actions.php",
        success: function(msg){
            $('#button_start').removeAttr("disabled");
            $('#button_stop').attr('disabled',"disabled");
            
            $('#button_refresh').attr('disabled',"disabled");
            $('#button_clear').attr('disabled',"disabled");
            
            append_log(msg);
        }
    });
	
}

function start_mon() 
{
    var inter = $('#interfaces').val();
    if(inter=='')
        alert("No interface selected...");
    else
    {
        $.ajax({
            type: "GET",
            data: "mon_start&interface="+inter,
            url: "reaver_actions.php",
            success: function(msg){
                append_log(msg);
                refresh_monitors();			
            }
        });
    }
}

function stop_mon() 
{
    var inter = $('#mon').val();
    if(inter=='')
        alert("No monitor interface selected...");
    else
    {
        $.ajax({
            type: "GET",
            data: "mon_stop&interface="+inter,
            url: "reaver_actions.php",
            success: function(msg){
                append_log(msg);
                refresh_monitors();			
            }
        });
    }
}


function append_log(line)
{
    $("#log").val( $("#log").val()+"\n"+line).scrollTop($("#log")[0].scrollHeight - $("#log").height());
}


function install_reaver()
{
    $.ajax({
        type: "GET",
        data: "reaver&install",
        url: "reaver_actions.php",
        success: function(msg){
            append_log(msg);
            refresh_monitors();			
        }
    });
}


