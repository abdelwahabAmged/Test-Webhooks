<?php

namespace PWA\Import\Model;

class Updater
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function startImport($command)
    {
        return $this->call($this->getUrl('start'), [
            'command' => $command
        ]);
    }

    public function getLog($offset, $limit)
    {
        $url = $this->getUrl('log');
        $params = [];
        if ($offset > 0) {
            $params['offset'] = (int)$offset;
        }
        if ($limit > 0) {
            $params['limit'] = (int)$limit;
        }
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $this->call($url);
    }

    public function getStatus()
    {
        return $this->call($this->getUrl('status'));
    }

    protected function getUrl($endpoint)
    {
        $baseUrl = $this->config->getBaseEndpoint();
        $url = $baseUrl . '/' . $endpoint;
        if (strpos($url, '/') !== false) {
            $url = preg_replace('/\/\/+/', '/', $url);
        }
        return $url;
    }

    protected function call($url, $body = null)
    {
        $ch = curl_init();
        if (is_array($body)) {
            if (!isset($body['key'])) {
                $body['key'] = $this->config->getKey();
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        } else {
            if (strpos($url, '?') !== false) {
                $url .= '&key=' . urlencode($this->config->getKey());
            } else {
                $url .= '?key=' . urlencode($this->config->getKey());
            }
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $decodedResult = json_decode($result);

        if (!empty($error)) {
            throw new HttpException($decodedResult, $error, $code);
        }
        if ($code != 200) {
            throw new HttpException($decodedResult, 'Request to PWA API returned ' . $code . ' response code', $code);
        }

        return $decodedResult;
    }
}
