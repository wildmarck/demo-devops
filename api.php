<?php

class MeteoAPI {
    private $apiKey = "6c1cd9d1b0184982a8b794509ffa7a78"; // ma clé OpenWeatherMap
    private $baseUrl = "https://api.openweathermap.org/data/2.5/";

    public function getMeteo($ville) {
        $url = "$this->baseUrl/weather?q=$ville&appid=$this->apiKey&units=metric&lang=fr";
        $response = file_get_contents($url);
        
        if ($response === false) {
            return json_encode(["error" => "Impossible de récupérer les données météo"]);
        }
        return $response;
    }

    public function getPrevisions($ville) {
        $url = "$this->baseUrl/forecast?q=$ville&appid=$this->apiKey&units=metric&lang=fr";
        $response = file_get_contents($url);
        
        if ($response === false) {
            return json_encode(["error" => "Impossible de récupérer les prévisions"]);
        }
        return $response;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ville'])) {
    $ville = htmlspecialchars($_GET['ville']);
    $meteoAPI = new MeteoAPI();
    
    header("Content-Type: application/json");
    if (isset($_GET['previsions'])) {
        echo $meteoAPI->getPrevisions($ville);
    } else {
        echo $meteoAPI->getMeteo($ville);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Requête invalide. Paramètre 'ville' requis."]);
}

