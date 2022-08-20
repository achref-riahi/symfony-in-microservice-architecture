<?php

namespace App\Protobuf;

use Grpc\BaseStub;
use Grpc\ChannelCredentials;

class GRPCClientFactory
{
    /**
     * Create gRPC client.
     *
     * @param string $className
     * @param string $hostname
     * @param string $port
     * @param string $credentials
     * @return BaseStub
     */
    public static function createGRPCClient(string $className, string $hostname, string $port, string $credentials): BaseStub
    {
        // @todo Handle all type of connection by switch, createInsecure, createSsl(file_get_contents("app.crt"))...
        $credentialsConfig = ['credentials' => ChannelCredentials::createInsecure()];

        return new $className(gethostbyname($hostname).':'.$port, $credentialsConfig);
    }
}
