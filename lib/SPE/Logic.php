<?php
namespace SPE;
class Logic{
    static public function getPubKey($str, $disable_local=true){
        $matches = null;
        if (preg_match("/^([a-zA-Z0-9_]+):([0-9]+)$/", $str, $matches)) {
            $github_user_name = $matches[1];
            $row_num = $matches[2];
            $key_list_url = "https://github.com/{$github_user_name}.keys";
            try {
                $key_list_str = file_get_contents($key_list_url);
            } catch (\Exception $e) {
                throw new \Exception('get github key fail. maybe wrong user name');
            }
            $key_list = explode("\n", $key_list_str);
            if (!isset($key_list[(int)$row_num])) {
                throw new \Exception('key row num is invalid');
            }
            $ssh_pub_key = $key_list[(int)$row_num];
        } elseif (preg_match("/^(ssh-rsa .*)$/", $str, $matches)) {
            $ssh_pub_key = $matches[1];
        } elseif (!$disable_local && (file_exists($str) && filesize($str) < MAX_PUB_KEY_FILE_SIZE) ) {
            $ssh_pub_key = file_get_contents($str);
        } else {
            throw new \Exception('any key found');
        }
        return $ssh_pub_key;
    }

    static public function encodeBase64($str){
        return chunk_split(base64_encode($str), 76, "\n");
    }
}