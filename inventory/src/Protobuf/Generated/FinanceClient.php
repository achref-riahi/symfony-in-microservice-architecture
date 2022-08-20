<?php
// GENERATED CODE -- DO NOT EDIT!

namespace App\Protobuf\Generated;

/**
 */
class FinanceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \App\Protobuf\Generated\GetExchangeRateRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function getExchangeRate(\App\Protobuf\Generated\GetExchangeRateRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/app.Finance/getExchangeRate',
        $argument,
        ['\App\Protobuf\Generated\GetExchangeRateResponse', 'decode'],
        $metadata, $options);
    }

}
