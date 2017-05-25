<?php
namespace TakaakiMizuno\Box\Http;

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
                    $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            }
        }

        $context = array(
            'http' => array(
                'method'  => strtoupper($request->getMethod()),
                'header'  => implode("\r\n", $this->getHeaderArray($headers)),
                'content' => $query,
            ),
        );

        $content = file_get_contents($url, false, stream_context_create($context));

        return new Response($this->parseHeaders($http_response_header), $content);
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
