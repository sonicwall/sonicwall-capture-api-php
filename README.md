# Steps to using this php sdk

## 1. Git clone or download file

   git clone ......

## 2. Include sdk

    require_once "./capture_api_client.php";

## 3. Prepare server/sn/api_key/base_path and new client object

    Syntax:
    $foo = new SNWL_CaptureServiceAPI($server, $sn, $api_key, $base_path = "/external/v1");

    For example:
    $foo = new SNWL_CaptureServiceAPI('...', '...', '...');

## 4. Common return result format

    array(
        'http_status' => 200, // number, required
        'content-type' => 'application/json', // string, required
        'headers' => '', // raw string without parsed, optional
        'body' => ... // json_object, optional
        'file_path' => './xxx.zip', // string, optional, for download api
    );

## 5. API List

### 5.1. list

    // @param number $after, optional
    // @param number $before, optional
    // @param number $page_size, optional
    // @param number $page_index, optional
    $ret = $foo->list($after, $before, $page_size, $page_index);

### 5.2 report

    // @param string $resource, required
    // @param bool $all_info, optional
    $ret = $foo->report($resource, $all_info);

### 5.3 artifact

    // @param string $sha256, required
    $ret = $foo->artifact($sha256);

### 5.4 scan

    // @param string $file_path, required
    $ret = $foo->scan($file_path);

### 5.5 down

    // @param string $sha256, required
    // @param string $engine, required
    // @param string $env, required
    // @param string $type, required
    // @param string $save_dir, required
    $ret = $foo->down($sha256, $engine, $env, $type, $save_dir);

## 6. CLI

### CLI format

  >php capture_api_cli.php --server=111 --sn=111 --api_key=111 --api=list ...

#### list

  >php capture_api_cli.php --server="..." --sn="..." --api_key="..." --api=list --after 0 --before 1557123928 --page_size 10 --page_index 1

#### artifact

  >php capture_api_cli.php --server="..." --sn="..." --api_key="..." --api=artifact --sha256=...

#### scan

  >php capture_api_cli.php --server="..." --sn="..." --api_key="..." --api=scan --file_path="..."

#### report

  >php capture_api_cli.php --server="..." --sn="..." --api_key="..." --api=report --resource=... --all_info

#### download

  >php capture_api_cli.php --server="..." --sn="..." --api_key="..." --api=download --sha256=... --engine=s --env=win_amd64 --type=pcap --save_dir=./
