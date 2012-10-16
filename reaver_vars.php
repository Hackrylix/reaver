<?php

require_once('reaver_functions.php');

//config start
$module_name = "Reaver";
$module_path = exec("pwd") . "/";
$module_version = "0.3";
//config end
//interfaces start
//$wifi_interfaces = getWirelessInterfaces();
//$monitored_interfaces = getMonitoredInterfaces();
//$interfaces = explode("\n", trim(shell_exec("cat /proc/net/dev | tail -n +3 | cut -f1 -d: | sed 's/ //g'")));
//$nbr_wifi_devices = exec("uci -P /var/state -q show wireless | grep wifi-device | wc -l");
//interfaces stop

//reaver exec start

//Test if reaver is installed
$is_reaver_installed = isInstalled("reaver");

//Test if reaver is running
//$is_reaver_running = isRunning("reaver");

//Test if reaver is active on boot
//$is_reaver_onboot = exec("cat /etc/rc.local | grep reaver/autostart.sh") != "" ? 1 : 0;
//reaver exec end
//
//USB related start
//$is_log_usb = file_exists("/usb/data/reaver/log/") ? 1 : 0;
//$is_symlink = exec("ls -l ".$module_path."log | sed 's/.*->\ //g'") == "/usb/data/reaver/log/" ? 1 : 0;
//if(!$is_symlink && $is_log_usb) exec("rm -rf ".$module_path."log && ln -s /usb/data/reaver/log ".$module_path."log &");
//USB related end
//auto start start
//$is_executable = exec("if [ -x ".$module_path."autostart.sh ]; then echo '1'; fi") != "" ? 1 : 0;
//if(!$is_executable) exec("chmod +x ".$module_path."autostart.sh");
//auto start end
//arrays
$modes = array(
    "Access Point" => "ap",
    "Client" => "sta",
    "Ad-Hoc" => "adhoc"
);

$security_modes = array(
    "Disabled" => "none",
    "WEP" => "wep",
    "WPA Personal" => "psk",
    //"WPA Enterprise" => "wpa",  
    "WPA2 Personal" => "psk2",
    //"WPA2 Enterprise" => "wpa2",
    "WPA/WPA2 Personal mixed mode" => "mixed-psk",
        //"WPA/WPA2 Enterprise mixed mode" => "mixed-wpa"
);

$wep_modes = array(
    "Shared key" => "shared",
    "Open System" => "open"
);

$eap_types = array(
    "TLS" => "tls",
    "PEAP" => "ttls"
);

$ciphers = array(
    "TKIP" => "tkip",
    "AES" => "aes",
    "TKIP / AES" => "tkip+aes"
);

$ssid_broadcast = array(
    "Enable" => "0",
    "Disable" => "1"
);
?>
