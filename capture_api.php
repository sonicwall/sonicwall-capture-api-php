<?php
/**
 * SDK For Capture
 *
 * @author      SonicWall
 * @version     1.0
 */
class SNWL_CaptureServiceAPI
{
    private $sn = false;
    private $api_key = '';
    private $cookie = false;

    /**
     * $server, // string, required
     * $sn, // string, required
     * $api_key, // string, required
     * $base_path = "/external/v1", // string, optional
     */
    public function __construct($server, $sn, $api_key, $base_path = "/external/v1")
    {
        $this->server = $server;
        $this->base_path = $base_path;
        $this->sn = $sn;
        $this->api_key = $api_key;
    }

    private function request($method, $url, $files, $timeout, $fp)
    {
        $ch = curl_init();

        $post_file = false;
        $user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Safari/537.36";
        $header[] = "Authorization: Basic " . base64_encode($this->sn . ":" . $this->api_key);

        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $ssl = preg_match('/^https:\/\//i', $url) ? true : false;
        if ($ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            // curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        }

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);

            if (!empty($files)) {
                $post_file = true;
                curl_setopt($ch, CURLOPT_POSTFIELDS, $files);
            }
        }

        if ($post_file === true) {
            $header[] = "Expect: 100-continue";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        if (!empty($this->cookie)) {
            curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        }

        if (!empty($fp)) {
            curl_setopt($ch, CURLOPT_HEADER, false);
            //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        } else {
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            exit("error" . curl_error($ch));
        }

        if (empty($response)) {
            curl_close($ch);
            exit("request error");
        }

        // echo var_dump($response);

        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        if ($post_file === true) {
            list($header100, $header, $body) = explode("\r\n\r\n", $response);
        } else {
            list($header, $body) = explode("\r\n\r\n", $response);
        }

        // parse cookie
        preg_match("/set\-cookie:([^\r\n]*)/i", $header, $matches);
        if (!empty($matches)) {
            $cookie = $matches[1];
            if (!empty($cookie)) {
                $this->cookie = $matches[1];
            }
        }

        curl_close($ch);

        $ret = array(
            'http_status' => $http_status,
            'content-type' => $content_type,
        );

        if (empty($fp)) {
            $ret['headers'] = $header;
            if (!empty($body) && ($content_type === 'application/json')) {
                $ret['body'] = json_decode($body);
            } else {
                $ret['body'] = $body;
            }
        }

        return $ret;
    }

    private function snwl_join_paths(...$paths)
    {
        return preg_replace('~[/\\\\]+~', DIRECTORY_SEPARATOR, implode(DIRECTORY_SEPARATOR, $paths));
    }

    private function _send_request($method, $path, $files = null, $timeout = 30, $fp = null)
    {
        $url = $this->server . $this->base_path . $path;
        $resp = $this->request($method, $url, $files, $timeout, $fp);

        return $resp;
    }

    /**
     * Scan API
     *
     * @access public
     * @param string $file_path, required
     * @return array
     */
    public function scan($file_path)
    {
        if (!file_exists($file_path)) {
            throw new Exception('invalid file path');
        }

        $mime = mime_content_type($file_path);
        $info = pathinfo($file_path);
        $name = $info['basename'];

        $file_obj = new CURLFile($file_path, $mime, $name);

        $resp = $this->_send_request("POST", "/file/scan", array("file" => $file_obj));

        return $resp;
    }

    /**
     * Report API
     *
     * @access public
     * @param string $resource, required
     * @param bool $all_info, optional
     * @return array
     */
    public function report($resource, $all_info = false)
    {
        $allInfo = ($all_info === true) ? "true" : "false";

        $path = "/file/report?resource=" . $resource . "&all_info=" . $allInfo;

        $resp = $this->_send_request("GET", $path);

        return $resp;
    }

    /**
     * List API
     *
     * @access public
     * @param number $after, optional
     * @param number $before, optional
     * @param number $page_size, optional
     * @param number $page_index, optional
     * @return array
     */
    function list($after = null, $before = null, $page_size = null, $page_index = null) {
        $query_strs = array();

        if (!empty($after)) {
            array_push($query_strs, "after=" . $after);
        }

        if (!empty($before)) {
            array_push($query_strs, "before=" . $before);
        }

        if (!empty($page_size)) {
            array_push($query_strs, "page_size=" . $page_size);
        }

        if (!empty($page_index)) {
            array_push($query_strs, "page_index=" . $page_index);
        }

        $path = "/file/list";

        if (!empty($query_strs)) {
            $path .= "?" . implode("&", $query_strs);
        }

        $resp = $this->_send_request("GET", $path);

        return $resp;
    }

    /**
     * Artifact API
     *
     * @access public
     * @param string $sha256, required
     * @return array
     */
    public function artifact($sha256)
    {
        $path = "/file/artifact?sha256=" . $sha256;

        return $this->_send_request("GET", $path);
    }

    /**
     * Download API
     *
     * @access public
     * @param string $sha256, required
     * @param string $engine, required
     * @param string $env, required
     * @param string $type, required
     * @param string $save_dir, required
     * @return array
     */
    public function download($sha256, $engine, $env, $type, $save_dir)
    {
        if (!is_dir($save_dir)) {
            throw new Exception("invalid directory");
        }

        if ($type !== 'report' && $type !== 'pcap' && $type !== 'screenshots') {
            throw new Exception('"type" must be "report", "pcap" or "screenshots".');
        }

        $path = "/file/download?sha256=" . $sha256 . "&engine=" . $engine . "&env=" . $env . "&type=" . $type;
        $suffix = ($type === 'report' ? 'xml' : ($type === 'pcap' ? 'pcap' : 'zip'));
        $file_name = "/$sha256-$engine-$env-$type.$suffix";

        $file_path = $this->snwl_join_paths($save_dir, $file_name);

        $fp = fopen($file_path, "w+") or die("Unable to open file!");

        $resp = $this->_send_request("GET", $path, null, 300, $fp);

        $resp['file_path'] = $file_path;

        if ($resp['http_status'] != 200) {
            fclose($fp);

            return $resp;
        }

        fclose($fp);

        return $resp; // $file_path;
    }

}
