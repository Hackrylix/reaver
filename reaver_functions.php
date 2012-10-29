<?php
require_once("iwlist_parser.php");

function getModuleName()
{
    return getConf('moduleName');
}

function getModuleVersion()
{
    return getConf('moduleVersion');
}

function getModuleAuthor()
{
    return "Hackrylix";
}

function kbytes_to_string($kb)
{
    $units = array('TB', 'GB', 'MB', 'KB');
    $scale = 1024 * 1024 * 1024;
    $ui = 0;

    while (($kb < $scale) && ($scale > 1))
    {
        $ui++;
        $scale = $scale / 1024;
    }
    return sprintf("%0.2f %s", ($kb / $scale), $units[$ui]);
}

function getIpFromInterface($interface)
{
    return exec("ifconfig " . $interface . " | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}'");
}

/**
 * Test if a command is installed
 * @param String The command to check
 * @return true if installed or false if not
 * 
 */
function isInstalled($command)
{

    if (file_exists("/usb/usr/bin/$command") || file_exists("/usr/bin/$command"))
        return true;
    else
        return false;
}

/**
 * Test if a command is installed on usb
 * @param String The command to check
 * @return 2 if installed on usb | 1 if not on usb | 0 if not installed
 * 
 */
function isInstalledOnUsb($command)
{
    if (isInstalled($command))
    {
        if (file_exists("/usb/usr/bin/$command"))
            return 2;
        else if (file_exists("/usr/bin/$command"))
            return 1;
    }
    else
        return 0;
}

/**
 * Test if a command is running
 * @param String The command to check
 * @return true if running or false if not
 * 
 */
function isRunning($command)
{
    return exec("ps auxww | grep $command | grep -v -e grep | grep -v -e php") != "" ? true : false;
}

/**
 * Get wlan interfaces
 * @return Array Array of wlan interfaces or NULL if no interfaces found
 */
function getWirelessInterfaces()
{
    $cmd = trim(shell_exec("iwconfig 2> /dev/null | grep \"wlan*\" | grep -v \"mon*\" | awk '{print $1}'"));
    if ($cmd != "")
        return array_reverse(explode("\n", $cmd));
    else
        return NULL;
}

/**
 * Get enabled wlan interfaces
 * @return Array Array of wlan interfaces or NULL if no up interfaces found
 */
function getEnabledWirelessInterfaces()
{
    $cmd = trim(shell_exec("ifconfig 2> /dev/null | grep \"wlan*\" | grep -v \"mon*\" | awk '{print $1}'"));
    if ($cmd != "")
        return array_reverse(explode("\n", $cmd));
    else
        return NULL;
}

/**
 * Get monitored interfaces
 * @return Array Array of mon interfaces or NULL if no mon interfaces found
 */
function getMonitoredInterfaces()
{
    $cmd = trim(shell_exec("cat /proc/net/dev | tail -n +3 | cut -f1 -d: | sed 's/ //g' | grep mon"));
    if ($cmd != "")
        return explode("\n", $cmd);
    else
        return NULL;
}

/**
 * Check if usb is mounted
 * @return true if mounted else return false
 */
function isUsbMounted()
{
    return (exec("mount | grep \"on /usb\" -c") >= 1) ? true : false;
}

/**
 * install a command via opkg
 * @return the result of the command
 */
function install($command, $onusb = false)
{
    $dest = "";
    if ($onusb)
        $dest = "--dest usb";

    return shell_exec("opkg update && opkg install $dest $command ");
}

/**
 * Get the radio int list
 * @return string
 */
function getListRadio()
{
    $str = "";
    $wifi_interfaces = getWirelessInterfaces();
    $str.= '<table>';

    for ($i = 0; $i < count($wifi_interfaces); $i++)
    {
        $interface = $wifi_interfaces[$i];
        //$mac_address = exec("uci get wireless.radio" . $i . ".macaddr");
        //$disabled = exec("uci get wireless.radio" . $i . ".disabled");

        $mode = exec("uci get wireless.@wifi-iface[" . $i . "].mode");
        //$interface = exec("ifconfig | grep -i " . $mac_address . " | awk '{print $1}'");
        //$interface = $interface != "" ? $interface : "-";

        $disabled = exec("ifconfig  | grep " . $interface . " | awk '{ print $1}'");
        $disabled = $disabled != "" ? false : true;

        $str.='<tr>
                    <td>radio' . $i . '</td>
                    <td>' . $interface . ' (mode ' . $mode . ')</td>
                    <td>';
        if (!$disabled)
            $str.='<font color="lime"><strong>enabled</strong></font>&nbsp;[<a id="down_int" href="javascript:down_int(\'' . $interface . '\');">Disable</a>]';
        else
            $str.='<font color="red"><strong>disabled</strong></font>&nbsp;[<a id="enable_int" href="javascript:up_int(\'' . $interface . '\');">Enable</a>]';

        $str.='</td></tr>';
    }
    $str.= '</table>';
    return $str;
}

/**
 * Get the interface List
 * @return string
 */
function getListInterface()
{
    $str = "";

    $wifi_interfaces = getEnabledWirelessInterfaces();
    if ($wifi_interfaces == NULL)
    {
        $str.= 'No enabled wifi interface found...';
    }
    else
    {
        $str.='<select id="interfaces">';
        foreach ($wifi_interfaces as $value)
        {
            $str.='<option value="' . $value . '">' . $value . '</option>';
        }
        $str.='</select>&nbsp;';
        $str.='[<a id="start_mon" href="javascript:start_mon();">Start mon</a>]';
    }

    return $str;
}

/**
 * Get monitor interface list
 * @return string
 */
function getListMonitor()
{
    $str = "";
    $monitored_interfaces = getMonitoredInterfaces();
    if ($monitored_interfaces == NULL)
    {
        $str.='No monitor interface found...';
    }
    else
    {
        $str.='<select id="mon">';
        foreach ($monitored_interfaces as $value)
        {

            $str.='<option value="' . $value . '">' . $value . '</option>';
        }
        $str.='</select>&nbsp;';
        $str.='[<a id="stop_mon" href="javascript:stop_mon();">Stop mon</a>]';
    }
    return $str;
}

/**
 * Read the conf file and return the value matching with the passed parametre
 * @param String the key
 * @return String the value
 */
function getConf($k)
{
    //$configArray = explode("\n", trim(file_get_contents("reaver.conf")));
    $configArray = parse_ini_file("reaver.conf");
    return $configArray[$k];
}

/**
 * Edit the $k value and Write config in the config file
 * @param String $k The key to edit
 * @param String $val The new Value
 * @return string Error/Success message
 */
function setConf($k, $val)
{
    //$configArray = explode("\n", trim(file_get_contents("reaver.conf")));
    $configArray = parse_ini_file("reaver.conf");
    $configArray[$k] = $val;
    $ok = write_conf_file($configArray, "reaver.conf");
    if ($ok)
        return "Setting '$k' = '$val' updated !";
    else
        return "Error while saving settings";
}

/**
 * Read the conf file and return the associated array
 * @return Array the config Array
 */
function getConfMulti()
{
    return parse_ini_file("reaver.conf");
}

/**
 * Write modified config in the config file
 * @param Array array to write in file
 * @return string Error/Success message
 */
function setConfMulti($array)
{
    $configArray = parse_ini_file("reaver.conf");
    foreach ($array as $key => $value)
    {
        $configArray[$key] = $value;
    }
    $ok = write_conf_file($configArray, "reaver.conf");
    if ($ok)
        return "Settings updated !";
    else
        return "Error while saving settings";
}

/**
 * Write the giver configArray to the $path
 * @param ArrayAssoc $configArray the array to write
 * @param String $path the config file path
 * @return boolean true when writed or false if an error occurred
 */
function write_conf_file($configArray, $path)
{
    $content = "";

    foreach ($configArray as $key => $value)
    {
        $content.="$key=$value\n";
    }

    if (!$handle = fopen($path, 'w'))
    {
        return false;
    }

    if (!fwrite($handle, $content))
    {
        return false;
    }
    fclose($handle);
    return true;
}

function deleteLogFile($bssid)
{
    $file = getConf('logPath') . 'reaver-' . $bssid . '.log';
    if (file_exists($file))
    {
        if (exec('rm ' . $file))
            return "Log file deleted";
        else
            return "Error while deleting the logFile";
    }
    else
        return "no log file found";
}

function logFileExists($bssid)
{

    $file = getConf('logPath') . 'reaver-' . $bssid . '.log';
    if (file_exists($file))
        return "yes";
    else
        return "no";
}

function isVariableValable($varToCheck)
{
    return (isset($varToCheck) && $varToCheck != "") ? true : false;
}

function getAPList($interface)
{
    $iwlistparse = new iwlist_parser();
    $p = $iwlistparse->parseScanDev($interface);
    $str = "";
    if (!empty($p))
    {
        $str.= '<em>Click on a row to select the target AP</em>
                <table id="survey-grid" class="grid" cellspacing="0">
                    <tr class="header">
                        <td>SSID</td>
                        <td>BSSID</td>
                        <td>Signal level</td>
                        <td colspan="2">Quality level</td>
                        <td>Ch</td>
                        <td>Encryption</td>
                        <td>Cipher</td>
                        <td>Auth</td>
                </tr>';
    }
    else
    {
        $str.= "<em>No access-point found, please retry or change the wifi interface used (in left panel)...</em>";
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
        $str.='<tr class="odd" name="' . $p[$interface][$i]["ESSID"] . ',' . $p[$interface][$i]["Address"] . ',' . $p[$interface][$i]["Channel"] . '">
                <td>' . $p[$interface][$i]["ESSID"] . '</td>
                <td>' . $p[$interface][$i]["Address"] . '</td>
                <td>' . $p[$interface][$i]["Signal level"] . '</td>
                <td>' . $quality . '%</td>
                <td width="150">
                    <div class="graph-border">
                        <div class="graph-bar" style="width: ' . $quality . '%; background: ' . $graph . ';"></div>
                    </div>
                </td>
                <td>' . $p[$interface][$i]["Channel"] . '</td>';

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
                $str.= '<td>WPA,WPA2</td>';
            else if ($WPA2 != "")
                $str.= '<td>WPA2</td>';
            else if ($WPA != "")
                $str.= '<td>WPA</td>';
            else
                $str.= '<td>WEP</td>';

            $str.= '<td>' . $cipher . '</td><td>' . $auth_type . '</td>';
        }
        else
        {
            $str.='<td>None</td><td>&nbsp;</td><td>&nbsp;</td>';
        }

        $str.= '</tr>';
    }
    return $str;
}

function RefreshLog($bssid)
{
    $logFile = getConf('logPath') . 'reaver-' . $bssid . '.log';
    $cmd = "cat $logFile";
    exec($cmd, $output);
    $str = "";
    foreach ($output as $outputline)
    {
        $str.= ("$outputline\n");
    }
    return $str;
}

?>
