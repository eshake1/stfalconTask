<?php

namespace App\CoreBundle\Model\Api;

class Curl
{
    /**
     * @param string $url
     * @return array
     */
    public function sendRequest(string $url): array
    {
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

        $result = curl_exec($curl);

        if ($result === false) {
            throw new \InvalidArgumentException('Curl error: ' . curl_error($curl));
        }

        curl_close($curl);

        $parsedResult = json_decode($result, true);

        return $parsedResult;
    }
}
