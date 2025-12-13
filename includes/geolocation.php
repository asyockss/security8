<?php
function getLocationByIP($ip) {
    //используем freegeoip.app
    $url = "https://freegeoip.app/json/" . $ip;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if($response) {
        $data = json_decode($response, true);
        return [
            'city' => $data['city'] ?? 'Unknown',
            'country' => $data['country_name'] ?? 'Unknown',
            'lat' => $data['latitude'] ?? 0,
            'lon' => $data['longitude'] ?? 0,
            'ip' => $data['ip'] ?? $ip
        ];
    }
    
    return null;
}

function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    //расстояние в километрах по формуле Haversine
    $earthRadius = 6371; //радиус Земли в км
    
    $latFrom = deg2rad($lat1);
    $lonFrom = deg2rad($lon1);
    $latTo = deg2rad($lat2);
    $lonTo = deg2rad($lon2);
    
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
    
    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    
    return $angle * $earthRadius;
}

function checkPasswordExpiry($password_changed_at) {
    include("../settings/config.php");
    
    $changed_date = new DateTime($password_changed_at);
    $current_date = new DateTime();
    $interval = $changed_date->diff($current_date);
    
    return $interval->days >= PASSWORD_EXPIRE_DAYS;
}
?>