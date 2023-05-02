<?php

namespace App\Service;

use App\Models\VoiceRecord;
use Illuminate\Support\Facades\Artisan;

use PAMI\Client\Impl\ClientImpl;
use PAMI\Message\Action\OriginateAction;
use PAMI\Message\Action\LogoffAction;

class CollService
{
    public function collAsterisk(string $phoneManager, string $phone, int $voice_id, string $trunk_login)
    {
        $options = array(
            'host' => env('ASTERISK_HOST'),
            'scheme' => 'tcp://',
            'port' => env('ASTERISK_PORT'),
            'username' => env('ASTERISK_USERNAME'),
            'secret' => env('ASTERISK_SECRET'),
            'connect_timeout' => env('ASTERISK_CONNECT_TIMEOUT'),
            'read_timeout' => env('ASTERISK_READ_TIMEOUT')
        );
        $voice = VoiceRecord::find($voice_id);

        if ($voice->type !== 'type_text_voice') {
            $result = $this->callVoiceAsterisk($options, $voice->text, $phoneManager, $phone, $trunk_login);
        }
        else {
            $result = $this->collTextAsterisk($options, $voice->text, $phoneManager, $phone, $trunk_login);
        }
        return $result;
    }

    protected function callVoiceAsterisk(array $options, string $voice, string $phoneManager, string $phone, string $trunk_login)
    {
        try {
            $client = new ClientImpl($options);
            $client->open();

            $action = new OriginateAction("SIP/".$phone.'@'.$trunk_login);
            $action->setContext("outgoing");
            $action->setVariable('VOICE', $voice);
            $action->setVariable('MANAGER',$phoneManager);
            $action->setExtension('s');
            $action->setPriority('1');

            $client->send($action);

            $action2 = new LogoffAction;
            $client->send($action2);

            $client->close();
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
        return true;
    }

    protected function collTextAsterisk(array $options, string $voice, string $phoneManager, string $phone, string $trunk_login)
    {

        try {
            $actionid = md5(uniqid());
            $client = new ClientImpl($options);
            $client->open();

            $action = new OriginateAction("Local/" . $phoneManager . "@pabx");
            $action->setContext("pabx");
            $action->setExtension($phone);
            $action->setCallerId($phone);
            $action->setPriority('1');
            $action->setAsync(true);
            $action->setActionID($actionid);

            $client->send($action);

            $action2 = new LogoffAction;
            $client->send($action2);

            $client->close();
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
        return true;
    }


}

