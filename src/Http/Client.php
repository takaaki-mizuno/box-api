<?php
namespace TakaakiMizuno\Box\Http;

use TakaakiMizuno\Box\Exceptions\APIErrorException;
use TakaakiMizuno\Box\Exceptions\InvalidTokenException;
use TakaakiMizuno\Box\Exceptions\NetworkException;

class Client
{
    protected $userAgent = 'TakaakiMizuno-BOX-SDK/0.1';

    /**
     * Client constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws APIErrorException
     * @throws InvalidTokenException
     * @throws NetworkException
     */
    public function request(Request $request)
    {
        $url     = $request->getUrl();
        $query   = http_build_query($request->getParameters());
        $headers = $request->getHeaders();
        if (!array_key_exists('Agent', $headers) || !$headers['Agent']) {
            $headers['Agent'] = $this->userAgent;
        }

        if (!empty($query)) {
            switch ($request->getMethod()) {
                case 'get':
                case 'head':
                    $url .= '?'.$query;
                    $query = '';
                    break;
                case 'post':
                case 'put':
                case 'patch':
                    if ($request->hasFile() || $request->getContentType() == 'multipart/form-data') {
                        $boundary                = '--------------------------'.microtime(true);
                        $headers['Content-Type'] = 'multipart/form-data; boundary='.$boundary;
                        $query                   = $this->createMultiPartBody($request, $boundary);
                    } elseif ($request->getContentType() == 'application/json') {
                        $headers['Content-Type'] = 'application/json';
                        $query                   = json_encode($request->getParameters());
                    } else {
                        $headers['Content-Type'] = 'application/x-www-form-urlencoded';
                    }
                    break;

            }
        }

        $context = array(
            'http' => array(
                'method'        => strtoupper($request->getMethod()),
                'header'        => implode("\r\n", $this->getHeaderArray($headers)),
                'content'       => $query,
                'ignore_errors' => true,
            ),
        );

        $content = file_get_contents($url, false, stream_context_create($context));

        if (preg_match('/HTTP\/1\.[0|1|x] ([0-9]{3})/', $http_response_header[0], $matches)) {
            $statusCode = (int) $matches[1];
            if ($statusCode == 401) {
                throw new InvalidTokenException('Invalid Token', 401);
            } elseif ($statusCode > 399) {
                print $url;
                print_r($http_response_header);
                throw new APIErrorException('API returns error', $statusCode);
            }
        } else {
            throw new NetworkException('Network Error', 0);
        }

        return new Response($this->parseHeaders($http_response_header), $content);
    }

    public function requestWithCurl(Request $request)
    {
        $url     = $request->getUrl();
        $query   = http_build_query($request->getParameters());
        $headers = $request->getHeaders();
        if (!array_key_exists('Agent', $headers) || !$headers['Agent']) {
            $headers['Agent'] = $this->userAgent;
        }

        $header = $this->getHeaderArray($headers);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);

        $data = curl_exec($ch);

        $info   = curl_getinfo($ch);
        $header = substr($data, 0, $info['header_size']);
        $body   = substr($data, $info['header_size']);

        curl_close($ch);

        print_r(explode("\r\n", $header));

        return new Response($this->parseHeaders(explode("\r\n", $header)), $body);
    }

    /**
     * @param Request $request
     * @param string  $boundary
     *
     * @return string
     */
    private function createMultiPartBody($request, $boundary)
    {
        $body = '--'.$boundary."\r\n"."Content-Disposition: form-data; name=\"attributes\"\r\n\r\n".json_encode($request->getParameters())."\r\n";

        foreach ($request->getFiles() as $filename) {
            $file_contents = file_get_contents($filename);
            $body .= '--'.$boundary."\r\n".'Content-Disposition: form-data; name="file"; filename="'.basename($filename)."\"\r\n"."Content-Type: application/octet-stream\r\n\r\n".$file_contents."\r\n";
        }
        $body .= '--'.$boundary."--\r\n";

        return $body;
    }

    private function getHeaderArray($headers)
    {
        $ret = array();
        foreach ($headers as $key => $value) {
            $ret[] = $key.': '.$value;
        }

        return $ret;
    }

    private function parseHeaders($headers)
    {
        $head = array();
        foreach ($headers as $k => $v) {
            $t = explode(':', $v, 2);
            if (isset($t[1])) {
                $head[trim($t[0])] = trim($t[1]);
            } else {
                $head[] = $v;
                if (preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $v, $out)) {
                    $head['responseCode'] = intval($out[1]);
                }
            }
        }

        return $head;
    }
}
