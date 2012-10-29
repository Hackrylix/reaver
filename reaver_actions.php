<?php

require_once("reaver_functions.php");

if (isset($_GET['reaver']))
{
    if (isset($_GET['install']))
    {
        $usb = (isset($_GET['onusb'])) ? true : false;
        echo install("reaver", $usb);
    }
    else if (isInstalled("reaver"))
    {
        if (isset($_GET['refresh']))
        {
            if (isVariableValable($_GET['bssid']))
                echo refreshLog($_GET['bssid']);
            else
                echo "No BSSID provided...";
        }
        else if (isset($_GET['start']))
        {
//            if (isRunning('reaver') == "1")
//                exec('killall reaver && wait 5');

            $victime = $_GET['victime'];
            $int = $_GET['interface'];

            if ($victime != "" && $int != "")
            {
                $logFile = getConf('logPath') . 'reaver-' . $victime . '.log';
                $options = "";
                $cmd = "reaver -i $int -b $victime ";

                if (isset($_GET['c']) && $_GET['c'] == "true")
                {
                    if (isset($_GET['ch']) && $_GET['ch'] != "")
                    {
                        $cmd .=" -f -c " . $_GET['ch'];
                        $options.="c";
                    }
                }

                if (isset($_GET['S']) && $_GET['S'] == "true")
                {
                    $cmd .=" -S ";
                    $options.="S";
                }
                if (isset($_GET['a']) && $_GET['a'] == "true")
                {
                    $cmd .=" -a ";
                    $options.="a";
                }
                $cmd .= " -vv >> $logFile -D &";

                exec($cmd);

                echo "Attack Started !";
                echo "\n$cmd";
                $conf = array('lastVictime' => $victime, 'lastOptions' => $options);
                setConfMulti($conf);
            }
            else
            {
                echo "no target ($victime) or interface ($int) provided";
            }
        }
        else if (isset($_GET['stop']))
        {
            exec("kill `ps -ax | grep reaver | grep -v -e grep | grep -v -e tail | grep -v -e logread | grep -v -e php | awk {'print $1'}`");

            echo "Attack Stopped !";
        }
    }
    else
    {
        echo 'reaver is not installed...';
    }
}
else if (isVariableValable($_GET['interface']))
{
    $interface = $_GET['interface'];
    if (isset($_GET['up']))
    {
        shell_exec("ifconfig " . $interface . " up &");
        echo "$interface up";
    }
    else if (isset($_GET['down']))
    {
        shell_exec("ifconfig " . $interface . " down &");
        echo "$interface down";
    }
    else if (isset($_GET['mon_start']))
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
        echo getAPList($interface);
    }
}
else if (isset($_GET['list']))
{
    if (isset($_GET['radio']))
        echo getListRadio();
    else if (isset($_GET['int']))
        echo getListInterface();
    else if (isset($_GET['mon']))
        echo getListMonitor();
}
else if (isset($_GET['log']))
{
    if (isVariableValable($_GET['action']))
    {
        $action = $_GET['action'];
        if ($action == "delete")
        {
            if (isVariableValable($_GET['bssid']))
                echo deleteLogFile($_GET['bssid']);
            else
                echo "No BSSID provided";
        }
        else if ($action == "check")
        {
            if (isVariableValable($_GET['bssid']))
                echo logFileExists($_GET['bssid']);
            else
                echo "No BSSID provided";
        }
        else if ($action == "move")
        {
            if (isVariableValable($_GET["dest"]))
            {
                $dest = $_GET['dest'];
                $logPath = getConf('logPath');
                if ($dest == "usb")
                {
                    if (isUsbMounted())
                    {
                        if (strstr($logPath, 'usb'))
                            echo "Already on usb";
                        else
                        {
                            $newPath = "/usb/data/reaver/logs/";
                            if (!is_dir($newPath))
                                if (!exec("mkdir -p $newPath"))
                                    echo "Error while creating usb log directory !";


                            if (exec('mv ' . $logPath . 'reaver-* ' . $newPath) == "")
                            {
                                setConf('logPath', $newPath);
                                echo 'Logs moved to usb';
                            }
                            else
                            {
                                echo 'error while moving to usb';
                            }
                        }
                    }
                    else
                        echo "No usb detected !";
                }
                else if ($dest == "internal")
                {
                    if (strstr($logPath, '/pineapple'))
                        echo "Already on internal";
                    else
                    {
                        $newPath = "/pineapple/logs/reaver/";
                        if (!is_dir($newPath))
                            if (!exec("mkdir -p $newPath"))
                                echo "Error while creating internal log directory !";

                        if (exec('mv ' . $logPath . 'reaver-* ' . $newPath) == "")
                        {
                            setConf('logPath', $newPath);
                            echo 'logs moved to internal';
                        }
                        else
                        {
                            echo 'error while moving to internal';
                        }
                    }
                }
            }
        }
    }
}
else
{
    echo "NO TRIGGER; please report to hackrylix@gmail.com...";
}
?>
