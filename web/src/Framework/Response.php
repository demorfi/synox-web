<?php

namespace Framework;

class Response
{
    /**
     * Set location header.
     *
     * @param string $url Location URL
     * @return null
     */
    public function location($url)
    {
        header('Location: ' . $url, true, 301);
        return (null);
    }

    /**
     * Print JSON.
     *
     * @param array $data Print data
     * @return null
     */
    public function json($data)
    {
        header('Content-Type: application/json');
        print (json_encode($data));
        return (null);
    }
}
