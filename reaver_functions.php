<?php

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
 * @return int 1 if true or 0 if false
 * 
 */
function isInstalled($command)
{
    return exec("which $command") != "*" ? 1 : 0;
}

/**
 * Test if a command is running
 * @param String The command to check
 * @return int 1 if true or 0 if false
 * 
 */
function isRunning($command)
{
    return exec("ps auxww | grep $command | grep -v -e grep | grep -v -e php") != "" ? 1 : 0;
}

/**
 * Get wlan interfaces
 * @return Array Array of wlan interfaces
 */
function getWirelessInterfaces()
{
    return array_reverse(explode("\n", trim(shell_exec("iwconfig 2> /dev/null | grep \"wlan*\" | grep -v \"mon*\" | awk '{print $1}'"))));
}

/**
 * Get monitored interfaces
 * @return Array Array of mon interfaces
 */
function getMonitoredInterfaces()
{
    return explode("\n", trim(shell_exec("cat /proc/net/dev | tail -n +3 | cut -f1 -d: | sed 's/ //g' | grep mon")));
}

?>
