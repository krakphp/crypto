<?php

namespace Krak\Crypto;

use Symfony\Component\Process;

class GnuPGCliCrypt implements Crypt
{
    private $user_name;
    private $passphrase;
    private $executable;

    public function __construct($user_name, $passphrase = null, $executable = 'gpg') {
        $this->user_name = $user_name;
        $this->passphrase = $passphrase;
        $this->executable = $executable;
    }

    public function encrypt($data) {
        $builder = new Process\ProcessBuilder([$this->executable, '-e', '-r', $this->user_name, '--batch', '--always-trust']);
        $builder->setInput($data);
        $process = $builder->getProcess();
        $process->run();
        if (!$process->isSuccessful()) {
            throw new Process\Exception\ProcessFailedException($process);
        }
        return $process->getOutput();
    }

    public function decrypt($data) {
        if ($this->passphrase === null) {
            throw new \LogicException('Cannot decrypt if no passphrase was set');
        }
        $outfile = tempnam(sys_get_temp_dir(), 'krak-crypto-gnupg-cli-');
        $builder = new Process\ProcessBuilder([$this->executable, '-d', '--passphrase', $this->passphrase, '--output', $outfile, '--batch', '--yes']);
        $builder->setInput($data);
        $process = $builder->getProcess();
        $process->disableOutput();
        $process->run();
        if (!$process->isSuccessful()) {
            throw new Process\Exception\ProcessFailedException($process);
        }
        $decrypted = file_get_contents($outfile);
        unlink($outfile);
        return $decrypted;
    }
}
