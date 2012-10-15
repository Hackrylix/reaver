<?php
require("reaver_vars.php");
require_once ("iwlist_parser.php");
?>
<html>
    <head>
        <title>Pineapple Control Center - <?php echo $module_name . " [v" . $module_version . "]"; ?></title>
        <script type="text/javascript" src="/includes/jquery.min.js"></script>
        <script type="text/javascript" src="js/reaver.js"></script>

        <link rel="stylesheet" type="text/css" href="/includes/styles.css" />
        <link rel="stylesheet" type="text/css" href="css/reaver.css" />

        <link rel="icon" href="/favicon.ico" type="image/x-icon">
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    </head>
    <body bgcolor="black" text="white" alink="green" vlink="green" link="green">
        <script type="text/javascript">
            $(document).ready(function(){ 
                init();
            });
        </script>

        <?php include("/pineapple/includes/navbar.php"); ?>

        <div class="sidepanelLeft" style="margin-top: 2px;">
            <div class="sidepanelTitle"><?php echo $module_name . " [v" . $module_version . "]"; ?></div>
            <div class="sidepanelContent">
                <?php
                /* @var $is_reaver_installed boolean */
                if ($is_reaver_installed)
                {
                    echo "reaver";
                    echo "&nbsp;<font color=\"lime\"><strong>installed</strong></font><br />";
                }
                else
                {
                    echo "reaver";
                    echo "&nbsp;<font color=\"red\"><strong>not installed</strong></font>";
                    echo '<input type="button" onclick="install_reaver()" value="install reaver" />';
                    echo '[<input id="onusb" type="checkbox" value="1" /> on usb]';
                    echo "<br /><br />";
                }

                echo '<hr />';
                echo 'Radio interfaces :<br /><div id="list_radio"></div><hr />';
                echo 'Available interfaces :<br /><div id="list_int"></div>
                    <hr />';
                echo 'Monitored interfaces :<br /><div id="list_mon"></div><hr />';
                echo 'Log :<br /><textarea id="log" disabled="disabled" cols="30" rows="20"></textarea>';
                echo '<div align="center" id="loading"><hr /><p><img src="loading.gif" /></p></div>';
                ?>


            </div>

        </div>
       
        <div class="content" style="margin-top: 5px;">
            <div class="contentTitle">Main</div>
            <div class="contentContent">
                <?php
                echo '<div>AP List | <input type="button" id="refresh_ap" onclick="refresh_available_ap();" value="Scan AP" /></div><br />';
                echo '<em>Click on row to select the target AP</em>';
                echo '<div id="list_ap"></div>';
                echo '<hr/>';
                ?>

                Victime :<br />
                <input type="text" disabled style="background-color: black; color: white;" id="ap" />
                <input type="text" disabled style="background-color: black; color: white;" id="victime" />
                 <input type="text" size="2" disabled style="background-color: black; color: white;" id="channel" />
                <input type="button" id="button_start" onclick="start_attack();" value="Attack target" />
                <input type="button" id="button_stop"  onclick="stop_attack();" value="Stop attack" />
                <br />
                <input type="checkbox" id="option_S" />&nbsp;Use small DH keys to improve crack speed<br />
                <input type="checkbox" id="option_a" />&nbsp;Auto detect the best advanced options for the target AP<br />
                <input type="checkbox" id="option_c" />&nbsp;Set the 802.11 channel for the interface (implies -f : Disable channel hopping)<br />
                <hr />
                Output :<br />
                <input type="button" id="button_refresh"  onclick="refresh_output();" value="Refresh output" />
                <input type="button" id="button_clear"  onclick="clear_output();" value="Clear output" /> Auto-refresh <select id="auto_time">
                    <option value="2000">2 sec</option>
                    <option value="5000">5 sec</option>
                    <option value="10000">10 sec</option>
                    <option value="15000">15 sec</option>
                    <option value="20000">20 sec</option>
                    <option value="25000">25 sec</option>
                    <option value="30000">30 sec</option>
                </select>
                <input type="button" id="start_ar" onclick="start_refresh();" value="On" /><input type="button" id="stop_ar" onclick="stop_refresh();" value="Off" />
                <br />
                <textarea id='output' disabled="disabled" name='output' cols='80' rows='24'></textarea>

            </div>

        </div>
   

</body>
</html>
