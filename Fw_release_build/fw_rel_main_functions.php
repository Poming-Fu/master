<?php

function generate_uuid($num) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $UUID = '';
    for ($i = 0; $i < $num; $i++) {
        $UUID .= $characters[rand(0, strlen($characters) - 1)];// - 1 為索引 ex 英文有26位，但位元從0開始，所以要0~25 => 0~(26-1)
    }
    return $UUID;
}

function alert($msg) {
    echo "<script type='text/javascript'>alert('$msg');</script>";
}

function update_jenkins_status() {
    $output = shell_exec(__DIR__ . "/fw_rel_get_jenkins_status.sh");
    return $output;
}

function execute_api($who, $branch, $platform, $ver, $option, $oemname, $UUID) {
    $script_path  = __DIR__ . "/fw_rel_form_api.sh";
    $args         = escapeshellarg($who) . ' ' . 
                    escapeshellarg($branch) . ' ' . 
                    escapeshellarg($platform) . ' ' . 
                    escapeshellarg($ver) . ' ' . 
                    escapeshellarg($option) . ' ' . 
                    escapeshellarg($oemname) . ' ' . 
                    escapeshellarg($UUID);

    $api_command  = "$script_path $args";
    $output       = shell_exec($api_command);

    return [
        'api_command'   => $api_command,
        'output'        => $output
    ];
}

?>