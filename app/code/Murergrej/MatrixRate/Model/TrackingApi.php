<?php

namespace Murergrej\MatrixRate\Model;

class TrackingApi
{
    const API_BASE_URL = 'http://api.pakkeplan.dk/api/booking/';

    protected $apiKey;
    protected $userID;

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function setUserID($userID)
    {
        $this->userID = $userID;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function getUserID()
    {
        return $this->userID;
    }

    public function getTrackingInformationList($ids)
    {
        return $this->callPost('GetTrackingInformationList', [
            'btNumbers' => $ids
        ]);
    }

    protected function callPost($endpoint, $parameters)
    {
        $url = self::API_BASE_URL . $endpoint . '?' . http_build_query(array_merge([
                'apikey' => $this->apiKey,
                'userID' => $this->userID
            ], $parameters));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt( $ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, true);
    }
}
