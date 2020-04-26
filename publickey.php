<?php 

namespace admobssv;

/**
 * 公钥处理
 * 从 AdMob 密钥服务器提取用于验证激励视频广告 SSV 回调的公钥列表。公钥列表以 JSON 表示形式提供。
 */

class PublicKey
{
    /**
     * AdMob 密钥服务器地址
     * @var string
     */
    private string $keys_url = 'https://www.gstatic.com/admob/reward/verifier-keys.json';


    /**
     * 公钥映射的标识
     * 在google回调中传参
     * @var string
     */
    private string $key_id='';

    /**
     * key_id 对应公钥的两种格式
     * @var array
     */
    private array $keys_map = ['pem'=> '', 'base64'=>''];


    /**
     * 实例化
     * @param string $key_id 公钥id
     */
    public function __construct(string $key_id='')
    {
        $key_id = trim($key_id);
        if (empty($key_id)) {
            throw new Exception("key_id error！");
        }
        $this->key_id = $key_id;
    }


    /**
     * 设置AdMob 密钥服务器地址
     * @param string $key_url 密钥服务器地址
     */
    public function setVerifierKeysUrl(string $key_url)
    {  
        $keys_url = trim($key_url);
        if (empty($keys_url) || !filter_var($keys_url, FILTER_VALIDATE_URL)) {
            throw new Exception("key_url error！");
        }
        $this->keys_url = $keys_url;
    }

    /**
     * 从 AdMob 密钥服务器提取公钥列表
     * 找到key_id映射的公钥
     * 
     * @return array
     */
    public function fetchVerifierKeys()
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,3);
        curl_setopt($ch, CURLOPT_URL, $this->keys_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);

        if(curl_errno($ch)){
            curl_close($ch);
            return false;
        }

        curl_close($ch);
        $result = json_decode($result, true);

        $keys_arr = [];
        foreach ($result['keys'] as $keys) {
            $keyId = trim($keys['keyId'] ?? '');
            if (empty($keyId)) {
                continue;
            }
            $keys_arr[$keyId]['pem'] = trim($keys['pem']);
            $keys_arr[$keyId]['base64'] = trim($keys['base64']);
        }

        if (!isset($keys_arr[$this->key_id])) {
            return false;
        }

        $this->keys_map = $keys_arr[$this->key_id];
    }

    /**
     * 从证书中解析获取公钥
     * 
     * @return resource
     */
    public function fetchPem()
    {
        if (empty($this->keys_map['pem'])) {
            $this->fetchVerifierKeys();
        }

        if (!isset($this->keys_map['pem'])) {
            return false;
        }
        return openssl_get_publickey(trim($this->keys_map['pem']));        
    }

    /**
     * 从base64字符串中解析获取公钥
     * @return resource
     */
    public function fetchBase64()
    {
        if (empty($this->keys_map['base64'])) {
            $this->fetchVerifierKeys();
        }

        if (!isset($this->keys_map['base64'])) {
            return false;
        }
        $pem = "-----BEGIN PUBLIC KEY-----\n" . wordwrap(trim($this->keys_map['base64']), 64, "\n", true) . "\n-----END PUBLIC KEY-----";

        return openssl_get_publickey($pem);
    }

}//end-class
