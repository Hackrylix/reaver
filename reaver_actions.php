<?php

require_once("iwlist_parser.php");
require("reaver_vars.php");

if (isset($_GET['reaver']))
{
    if (isset($_GET['install']))
    {
        echo shell_exec("opkg update && opkg install reaver &");
    }
    else if ($is_reaver_installed)
    {
        if (isset($_GET['refresh']))
        {
            if (isset($_GET['bssid']) && $_GET['bssid'] != "")
            {
                $bssid = $_GET['bssid'];
                $cmd = "cat /pineapple/logs/reaver-$bssid.log";

                exec($cmd, $output);
                foreach ($output as $outputline)
                {
                    echo ("$outputline\n");
                }
            }
            else
                echo "No BSSID provided...";
        }
        else if (isset($_GET['start']))
        {
            $victime = $_GET['victime'];
            $int = $_GET['interface'];



            if ($victime != "" && $int != "")
            {
                $cmd = "killall reaver && reaver -i $int -b $victime ";
                if(isset($_GET['S'])&&$_GET['S']=="true")
                    $cmd .=" -S ";
                if(isset($_GET['a'])&&$_GET['a']=="true")
                    $cmd .=" -a ";
                $cmd .= " -vv >> /pineapple/logs/reaver-$victime.log -D &";
                
                exec($cmd);
                
                //exec("/pineapple/modules/Bartender/projects/reaver/attack.sh $int $victime | at now");
                //echo 'command : '.$cmd;
                echo "Attack Started !";
            }
            else
            {
                echo "no target ($victime) or int ($int)";
            }
        }
        else if (isset($_GET['stop']))
        {
            echo exec("kill `ps -ax | grep reaver | grep -v -e grep | grep -v -e tail | grep -v -e logread | grep -v -e php | awk {'print $1'}`");
            echo "Attack Stopped !";
        }
    }
    else
    {
        echo 'reaver is not installed... <input type="button" onclick="install_reaver()" value="install reaver" />';
    }
}
else if (isset($_GET['interface']) && $_GET['interface'] != "")
{
    $interface = $_GET['interface'];
    if (isset($_GET['mon_start']))
    {
        shell_exec("airmon-ng start " . $interface . " &");
        echo "Monitor started on $interface";
    }
    else if (isset($_GET['mon_stop']))
    {
        shell_exec("airmon-ng stop " . $interface . " &");
        echo "Monitor stopped on $interface";
    }
    else if (isset($_GET['available_ap']))
    {
        // List APs
        $iwlistparse = new iwlist_parser();
        $p = $iwlistparse->parseScanDev($interface);

        if (!empty($p))
        {
            echo '<table id="survey-grid" class="grid" cellspacing="0">';
            echo '<tr class="header">';
            echo '<td>SSID</td>';
            echo '<td>BSSID</td>';
            echo '<td>Signal level</td>';
            echo '<td colspan="2">Quality level</td>';
            echo '<td>Ch</td>';
            echo '<td>Encryption</td>';
            echo '<td>Cipher</td>';
            echo '<td>Auth</td>';
            echo '</tr>';
        }
        else
        {
            echo "<em>No data...</em>";
        }

        for ($i = 1; $i <= count($p[$interface]); $i++)
        {
            $quality = $p[$interface][$i]["Quality"];

            if ($quality <= 25)
                $graph = "red";
            else if ($quality <= 50)
                $graph = "yellow";
            else if ($quality <= 100)
                $graph = "green";
            echo '<tr class="odd" name="' . $p[$interface][$i]["ESSID"] . ',' . $p[$interface][$i]["Address"] . '">';

            echo '<td>' . $p[$interface][$i]["ESSID"] . '</td>';
            $MAC_address = explode(":", $p[$interface][$i]["Address"]);


            echo '<td>' . $p[$interface][$i]["Address"] . '</td>';
            echo '<td>' . $p[$interface][$i]["Signal level"] . '</td>';
            echo "<td>" . $quality . "%</td>";
            echo "<td width='150'>";
            echo '<div class="graph-border">';
            echo '<div class="graph-bar" style="width: ' . $quality . '%; background: ' . $graph . ';"></div>';
            echo '</div>';
            echo "</td>";
            echo '<td>' . $p[$interface][$i]["Channel"] . '</td>';

            if ($p[$interface][$i]["Encryption key"] == "on")
            {
                $WPA = strstr($p[$interface][$i]["IE"], "WPA Version 1");
                $WPA2 = strstr($p[$interface][$i]["IE"], "802.11i/WPA2 Version 1");

                $auth_type = str_replace("\n", " ", $p[$interface][$i]["Authentication Suites (1)"]);
                $auth_type = implode(' ', array_unique(explode(' ', $auth_type)));

                $cipher = $p[$interface][$i]["Pairwise Ciphers (2)"] ? $p[$interface][$i]["Pairwise Ciphers (2)"] : $p[$interface][$i]["Pairwise Ciphers (1)"];
                $cipher = str_replace("\n", " ", $cipher);
                $cipher = implode(',', array_unique(explode(' ', $cipher)));

                if ($WPA2 != "" && $WPA != "")
                    echo '<td>WPA,WPA2</td>';
                else if ($WPA2 != "")
                    echo '<td>WPA2</td>';
                else if ($WPA != "")
                    echo '<td>WPA</td>';
                else
                    echo '<td>WEP</td>';

                echo '<td>' . $cipher . '</td>';
                echo '<td>' . $auth_type . '</td>';
            }
            else
            {
                echo '<td>None</td>';
                echo '<td>&nbsp;</td>';
                echo '<td>&nbsp;</td>';
            }

            echo '</tr>';
        }
    }
}
else if (isset($_GET['list']))
{
    if (isset($_GET['radio']))
    {
        echo '<table class="interfaces">';

        for ($i = 0; $i < $nbr_wifi_devices; $i++)
        {
            $mac_address = exec("uci get wireless.radio" . $i . ".macaddr");
            //$disabled = exec("uci get wireless.radio" . $i . ".disabled");

            $mode = exec("uci get wireless.@wifi-iface[" . $i . "].mode");
            $interface = exec("ifconfig | grep -i " . $mac_address . " | awk '{print $1}'");
            $interface = $interface != "" ? $interface : "-";

            $disabled = exec("ifconfig  | grep " . $interface . " | awk '{ print $1}'");
            $disabled = $disabled != "" ? false : true;

            echo '<tr>';

            echo '<td>radio' . $i . '</td>';
            echo '<td>' . $interface . '</td>';
            echo '<td>';
            if (!$disabled)
                echo '<font color="lime"><strong>enabled</strong></font>';
            else
                echo '<font color="red"><strong>disabled</strong></font>';
            echo '</td>';

            echo '</tr>';
        }
        echo '</table>';
    }
    else if (isset($_GET['int']))
    {
        if (count($wifi_interfaces) == 0)
        {
            echo 'No wifi interface found...';
        }
        else
        {
            echo '<select id="interfaces">';
            foreach ($wifi_interfaces as $value)
            {
                echo '<option value="' . $value . '">' . $value . '</option>';
            }
            echo '</select>&nbsp;';
            echo '[<a id="start_mon" href="javascript:start_mon();">Start mon</a>]';
        }
    }
    else if (isset($_GET['mon']))
    {
        if (count($monitored_interfaces) == 0)
        {
            echo 'No monitor interface found...';
        }
        else
        {
            echo '<select id="mon">';
            foreach ($monitored_interfaces as $value)
            {

                echo '<option value="' . $value . '">' . $value . '</option>';
            }
            echo '</select>&nbsp;';
            echo '[<a id="stop_mon" href="javascript:stop_mon();">Stop mon</a>]';
        }
    }
}
else
{
    echo "NO TRIGGER; please debug...";
}
?>