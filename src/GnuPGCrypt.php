<?php

namespace Krak\Crypto;

class GnuPGCrypt implements Crypt
{
    private $gpg;

    public function __construct($gpg) {
        $this->gpg = $gpg;
    }

    /**
     * $keys_config = [
     *     'public' => ['key' => 'data'],
     *     'private' => ['key' => 'data'], 'password' => '']
     *  ]
     * @param $keys_config
     */
    public static function createFromKeys($keys_config) {
        $pub_key   = $keys_config['public']['key'];
        $priv_key  = $keys_config['private']['key'];
        $priv_pass = $keys_config['private']['pass'];

        $gpg = new gnupg();
        $gpg->seterrormode(gnupg::ERROR_EXCEPTION);

        $pub_info = $gpg->import($pub_key);
        $priv_info = $gpg->import($priv_key);
        $pub_fingerprint = $pub_info['fingerprint'];
        $priv_fingerprint = $priv_info['fingerprint'];

        $gpg->addEncryptKey($pub_key, $pub_fingerprint);
        if ($priv_key) {
            $gpg->addDecryptKey($gpg, $priv_fingerprint, $priv_pass);
            return new self($gpg);
        }
        else{
            return new EncryptOnlyCrypt(
                new self($gpg)
            );
        }
    }

    public function encrypt($data) {
        return $this->gpg->encrypt($data);
    }

    public function decrypt($data) {
        return $this->gpg->decrypt($data);
    }
}
