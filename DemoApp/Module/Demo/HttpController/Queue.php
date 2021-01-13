<?php
namespace App\Module\Demo\HttpController;

use wrxswoole\Core\HttpController\CoreHttpController;
use wrxswoole\Core\Model\Producer;

class Queue extends CoreHttpController
{

    /**
     *
     * @Authenticate(false)
     */
    public function publish()
    {
        $this->writeSuccessJson([
            "consumer_response" => Producer::getInstance()->publish([
                [
                    'topic' => 'test',
                    'value' => 'test queue message',
                    'key' => 'key'
                ]
            ])
        ]);
    }
}