<?php

 namespace depakin\admobssv;

 use depakin\admobssv\PublicKey;

/**
 * 签名验证
 */
class Signature
{
    /**
     * google ssv回调GET参数查询字符串
     * @var string
     */
    private string $query_string = '';

    /**
     * 公钥keyId
     */
    private string $key_id = '';

    /**
     * 签名源数据
     * @var string
     */
    private string $message = '';

    /**
     * 签名结果字符串
     * @var string
     */
    private string $signature = '';

    /**
     * 签名验证返回值
     * @var array
     */
    private array $ret_verify = ['code'=> 0, 'message'=> 'success!'];

    /**
     * object publicKey
     * @var object
     */
    private $obj_publicKey = null;

    /**
     * 初始化签名
     * @param string $query_string 回调参数字符串
     */
    public function __construct(string $query_string='')
    {
        $query_string = trim($query_string);
        if (empty($query_string)) {
            throw new Exception('empty query_string!');
        }

        if (strpos($query_string, '&signature') === false || strpos($query_string, '&key_id') === false) {
            throw new Exception('error format query_string!');
        }
        $this->query_string = $query_string;
    }


    /**
     * 解析查询字符串
     * @return void
     */
    private function parseQueryString()
    {
        // 查询字符串解析到变量
        parse_str($this->query_string, $query_arr);

        // 公钥的key_id
        $this->key_id = trim($query_arr['key_id'] ?? '');
        // 签名
        $this->signature = trim($query_arr['signature']?? '');
        // 最关键的一点，是这个字符串的替换。不替换怎么做都是验证失败
        $this->signature = str_replace(['-', '_'], ['+', '/'], $this->signature);
        // 签名源数据
        $this->message = urldecode(substr($this->query_string, 0, strpos($this->query_string, '&signature')));

        if (empty($this->key_id) || empty($this->signature) || empty($this->message)) {
            throw new Exception('query_string Missing required parameters!');
        }
        // publicKey
        $this->obj_publicKey = new PublicKey($this->key_id);
    }

    /**
     * 验证签名
     * @return array
     */
    public function verify()
    {
        $this->parseQueryString();

        $publicKey = $this->obj_publicKey->fetchPem();

        if ( !is_resource($publicKey)) {
            $this->ret_verify['code'] = -2;
            $this->ret_verify['message'] = 'publicKey error!';

            return $this->ret_verify;
        }

        $result = openssl_verify($this->message, base64_decode($this->signature), $publicKey, OPENSSL_ALGO_SHA256);

        if ($result === 1) {
            return $this->ret_verify;
        }

        $this->ret_verify['code'] = -3;
        $this->ret_verify['message'] = openssl_error_string();

        return $this->ret_verify;
    }

}//endc-class
