<?php

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
    $cmd = trim(shell_exec("mount | grep 'on /usb'"));
    if ($cmd != "")
        return true;
    else
        return false;
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
        $configArray[$key]=$value;
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
?>
