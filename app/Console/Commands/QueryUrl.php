<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Exception;

class QueryUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'query:url {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets headers from given url, outputs and logs results';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $api = config('remoteApi.url.proxyscrape');
        $url = $this->argument('url');

        $proxies = $this->getProxy($api);
        $response = $this->requestUrl($url, $proxies);
        $this->consoleOutput($response);
        $this->logEvent($url);

        return 0;
    }

    /**
     * Queries an API to find an open proxy
     *
     * @param string $api
     * @return array
     */
    private function getProxy(string $api)
    {
        $proxies = [];
        try {
            $curl = curl_init($api);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($curl);
            curl_close($curl);

            $proxies = explode("\n", $response);
        } catch (Exception $e) {
            $this->error('Error while getting proxies: ' . $e->getMessage());
        }

        return $proxies;
    }

    /**
     * Makes a request to the given URI via an open proxy
     *
     * @param string $url
     * @param array $proxies
     * @return string
     */
    private function requestUrl(string $url, array $proxies)
    {
        try {
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

            return $response;
        } catch (Exception $e) {
            $this->error('Error while requesting url: ' . $e->getMessage());
        }
    }

    /**
     * Outputs the returned HTTP headers to the console
     *
     * @param string $response
     * @return void
     */
    private function consoleOutput(string $response)
    {
        try {
            $parts = explode("\r\n\r\n", $response, 2);
            $header = $parts[0];
            $this->line($header);
        } catch (Exception $e) {
            $this->error('Error outputting results: ' . $e->getMessage());
        }
    }

    /**
     * Logs the request to a file
     *
     * @param string $url
     * @return void
     */
    private function logEvent(string $url)
    {
        try {
            $now = date('d/m/Y H:i:s');
            file_put_contents(storage_path() . '/logs/results.log', "{$now}: {$url}\r\n", FILE_APPEND);
        } catch (Exception $e) {
            $this->error('Error outputting results: ' . $e->getMessage());
        }
    }
}
