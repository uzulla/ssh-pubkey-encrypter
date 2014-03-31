<?php
namespace Uzulla\Crypt;

class Misc {

    public static function encodeBySshPubKey($ssh_pub_key, $str){
        $pub_pem=\Uzulla\Crypt\Misc::openssh2pem($ssh_pub_key);
        $pub_key=openssl_pkey_get_public($pub_pem);
        if($pub_key===FALSE) echo "FAIL OPEN\n";
        $crypted = null;
        openssl_public_encrypt($str, $crypted, $pub_key, OPENSSL_PKCS1_PADDING);
        openssl_free_key($pub_key);
        if(is_null($crypted)){
            throw new \Exception('fail crypting');
        }
        return $crypted;
    }

    public static function decodeBySshPrivKey($ssh_priv_key, $pass_phrase, $str){
        $priv_key = openssl_pkey_get_private($ssh_priv_key, $pass_phrase);
        unset($pass_phrase);
        if($priv_key===false){
            throw new \Exception("Key open fail. maybe wrong pass phrase");
        }

        $plain = null;
        openssl_private_decrypt($str, $plain, $priv_key);
        openssl_free_key($priv_key);

        return $plain;
    }

    // http://stackoverflow.com/questions/3299003/how-to-convert-openssh-public-key-file-format-to-pem
    public static function len($s)
    {
        $len = strlen($s);

        if ($len < 0x80) {
            return chr($len);
        }

        $data = dechex($len);
        $data = pack('H*', (strlen($data) & 1 ? '0' : '') . $data);
        return chr(strlen($data) | 0x80) . $data;
    }

    // http://stackoverflow.com/questions/3299003/how-to-convert-openssh-public-key-file-format-to-pem
    public static function openssh2pem($ssh_pub_key)
    {
        list(,$data) = explode(' ', trim($ssh_pub_key), 3);
        $data = base64_decode($data);

        list(,$alg_len) = unpack('N', substr($data, 0, 4));
        $alg = substr($data, 4, $alg_len);

        if ($alg !== 'ssh-rsa') {
            return FALSE;
        }

        list(,$e_len) = unpack('N', substr($data, 4 + strlen($alg), 4));
        $e = substr($data, 4 + strlen($alg) + 4, $e_len);
        list(,$n_len) = unpack('N', substr($data, 4 + strlen($alg) + 4 + strlen($e), 4));
        $n = substr($data, 4 + strlen($alg) + 4 + strlen($e) + 4, $n_len);

        $algid = pack('H*', '06092a864886f70d0101010500');                // algorithm identifier (id, null)
        $algid = pack('Ca*a*', 0x30, static::len($algid), $algid);                // wrap it into sequence
        $data = pack('Ca*a*Ca*a*', 0x02, static::len($n), $n, 0x02, static::len($e), $e); // numbers
        $data = pack('Ca*a*', 0x30, static::len($data), $data);                   // wrap it into sequence
        $data = "\x00" . $data;                                           // don't know why, but needed
        $data = pack('Ca*a*', 0x03, static::len($data), $data);                   // wrap it into bitstring
        $data = $algid . $data;                                           // prepend algid
        $data = pack('Ca*a*', 0x30, static::len($data), $data);                   // wrap it into sequence

        return "-----BEGIN PUBLIC KEY-----\n" .
        chunk_split(base64_encode($data), 64, "\n") .
        "-----END PUBLIC KEY-----\n";
    }
}
