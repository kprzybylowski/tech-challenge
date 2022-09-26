<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class QueryUrl extends Command
{
    protected $signature = 'query:url {url}';

    protected $description = 'N/A';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Find a proxy.

        $curl = curl_init('https://api.proxyscrape.com/v2/?request=displayproxies&protocol=http&timeout=10000&country=all&ssl=yes&anonymity=all');

        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);

        curl_close($curl);

        $proxies = explode("\n", $response);

        // Make request.

        $url = $this->argument('url');

        foreach ($proxies as $proxy) {
            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_setopt($curl, CURLOPT_PROXY, $proxy);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($curl);

            curl_close($curl);

            if (!$response) {
                continue;
            }
        }

        // Output HTTP headers.

        $parts = explode("\r\n\r\n", $response, 2);

        $header = $parts[0];

        $this->line($header);

        // Log this request.

        $now = date('d/m/Y H:i:s');

        file_put_contents(storage_path() . '/logs/results.log', "{$now}: {$url}\r\n", FILE_APPEND);

        return 0;
    }
}
