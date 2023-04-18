<?php

namespace App\Action\Linkhouse;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class IndexAction
{   
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {   
        $url = 'https://app.linkhouse.co/rekrutacja/strony';

        $json = file_get_contents($url);
        $obj = json_decode($json);
        
        $requested_site = '';
        
        $traffic = [];
        $quality = [];
        $price = [];
        
        foreach($obj->sites as $site) {
          if ($site->site == $obj->requested_site) {
           $requested_site = $site;
          }
        
          $traffic[] = $site->traffic;
          $quality[] = $site->quality;
          $price[] = $site->price;
        }
        
        if ($requested_site == '') {
          $data = ["status" => false, "message" => "Requested site not found in the sites data!"];

          $payload = json_encode($data);
  
          $response->getBody()->write($payload);
  
          return $response->withHeader('Content-Type', 'application/json');
        }
        
        $dtraffic = max($traffic) - min($traffic);
        $dquality = max($quality) - min($quality);
        $dprice = max($price) - min($price);
        
        $diffs = [];
        
        foreach($obj->sites as $site) {
          if ($site->site !== $requested_site->site) {
            $diff = [];
        
            $diff['site'] = $site;
            $diff['value'] = abs($requested_site->traffic - $site->traffic) / $dtraffic + abs($requested_site->quality - $site->quality) / $dquality + abs($requested_site->price - $site->price) / $dprice;
        
            $diffs[] = $diff;
          }
        }
        
        usort($diffs, fn($a, $b) => $a['value'] <=> $b['value']);
        $results = array_splice($diffs, 0, 10);
        
        //print_r($requested_site);
        //print_r($results);

        $data['requested'] = (array) $requested_site;
        $data['sites'] = (array) $results;

        $payload = json_encode($data);

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
