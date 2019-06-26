
<?php

require_once "./capture_api.php";

/**
 * syntax:
 * >php capture_api_cli.php --server=... --sn=... --api_key=... --api=list ...
 */
$args = getopt('', array('server:', 'sn:', 'api_key:', 'api:'));

$foo = new SNWL_CaptureServiceAPI($args['server'], $args['sn'], $args['api_key']);

$api = $args['api'];
if ($api == 'list') {
    // php capture_api_cli.php --server="..." --sn="..." --api_key="..." --api=list --after 0 --before 1557123928 --page_size 10 --page_index 1
    $args = getopt('', array("after:", "before:", "page_size:", "page_index:"));
    echo json_encode($foo->list($args['after'], $args['before'], $args['page_size'], $args['page_index']), JSON_PRETTY_PRINT) . "\n";
} else if ($api == 'artifact') {
    // php capture_api_cli.php --server="..." --sn="..." --api_key="..." --api=artifact --sha256=...
    $args = getopt('', array("sha256:"));
    echo json_encode($foo->artifact($args['sha256']), JSON_PRETTY_PRINT) . "\n";
} else if ($api == 'scan') {
    // php capture_api_cli.php --server="..." --sn="..." --api_key="..." --api=scan --file_path="..."
    $args = getopt('', array("file_path:"));
    echo json_encode($foo->scan($args['file_path']), JSON_PRETTY_PRINT) . "\n";
} else if ($api == 'report') {
    // php capture_api_cli.php --server="..." --sn="..." --api_key="..." --api=report --resource=... --all_info
    $args = getopt('', array("resource:", "all_info"));
    echo json_encode($foo->report($args['resource'], isset($args['all_info'])), JSON_PRETTY_PRINT) . "\n";
} else if ($api == 'download') {
    // php capture_api_cli.php --server="..." --sn="..." --api_key="..." --api=download --sha256=... --engine=s --env=win_amd64 --type=pcap --save_dir=./
    $args = getopt('', array("sha256:", "engine:", "env:", "type:", "save_dir:"));
    echo json_encode($foo->download($args['sha256'], $args['engine'], $args['env'], $args['type'], $args['save_dir']), JSON_PRETTY_PRINT) . "\n";
}
