<?php
require_once 'bot-frzdp-id.php';
require_once 'bot-frzdp-fungsi.php';
require_once 'bot-frzdp-proses.php';

function myloop()
{
    global $debug;

    $idfile = 'botposesid.txt';
    $update_id = 0;

    if (file_exists($idfile)) {
        $update_id = (int) file_get_contents($idfile);
        echo '-';
    }

    $updates = getApiUpdate($update_id);

    foreach ($updates as $message) {
        $update_id = prosesApiMessage($message);
        echo '+';
    }
    file_put_contents($idfile, $update_id + 1);
}

while (true) {
    myloop();
}
