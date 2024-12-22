<?php
header('Content-Type: text/vnd.wap.wml'); // Many devices require correct Content-Type.

function getFormattedTime($isoTime)
{
    $dateTime = new DateTime($isoTime);
    return $dateTime->format('H:i');
}

$wv_codes = array(
    0    => "Clear sky",
    1    =>    "Mainly clear",
    2    => "Partly cloudly",
    3    =>    "Overcast",
    // Fog
    45    =>    "Foggy",
    48    =>    "Cold fog",
    // Drizzle
    51    =>    "Light drizzle",
    53    =>    "Moderate drizzle",
    55    =>    "Dense drizzle",
    // Freezing Drizzle
    56    =>    "Light freezing drizzle",
    57    =>    "Dense freezing drizzle",
    // Rain
    61    =>    "Slight rain",
    63    =>    "Moderate rain",
    65    =>    "Heavy rain",
    // Freezing Rain
    66    =>    "Light freezing rain",
    67    =>    "Heavy freezing rain",
    // Snow
    71    =>    "Slight snow",
    73    =>    "Moderate snow",
    75    =>    "Heavy snow",
    77    =>    "Snow grains",
    // Rain showers
    80    =>    "Slight showers",
    81    =>    "Moderate showers",
    82    =>    "Violent showers",
    // Snow showers
    85    =>    "Slight snow showers",
    86    =>    "Heavy snow showers",
    // Thunderstorm
    95    =>    "Thunderstorms",
    // Thunderstorm + hail
    96    =>    "Thunderstorms, slight hail",
    99    =>    "Thunderstorms, heavy hail"
);

$locId = htmlentities($_GET['id']);
if ($locId == "emf") { // Very hacky way to have a keywork for EMF Camp.
    $location = array(
        "latitude"    =>    52.03935809030366,
        "longitude"    =>    -2.378456567704384,
        "elevation"    =>    105,
        "name"    =>     "EMF Camp",

    );
} else {
    $location = json_decode(file_get_contents("https://geocoding-api.open-meteo.com/v1/get?id=" . $locId), true);
}

$locName = $location["name"];
$isF = false;
$url = "https://api.open-meteo.com/v1/forecast";
// Add the latitude and longitude
$url = $url . "?latitude=" . $location["latitude"] . "&longitude=" . $location["longitude"] . "&elevation=" . $location["elevation"];
// Add all other options
$url = $url . "&current=temperature_2m,relative_humidity_2m,weather_code&daily=weather_code,temperature_2m_max,temperature_2m_min,sunrise,sunset,uv_index_max&timezone=auto&forecast_days=3";
// Add a case for if a user wants Fahrenheit (weirdos!)
if ($isF) {
    $url = $url . "&temperature_unit=fahrenheit";
}

$apiRes = json_decode(file_get_contents($url), true);
?>

<?= '<?xml version="1.0"?>' ?>
<!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml">
<wml>

    <card id='current' title='<?= $locName; ?> Weather'>
        <p>Current temperature: <?= $apiRes["current"]["temperature_2m"]; ?> <?= $apiRes["current_units"]["temperature_2m"]; ?></p>
        <p>Current humidity: <?= $apiRes["current"]["relative_humidity_2m"]; ?>%</p>
        <p>Current weather: <?= $wv_codes[$apiRes["current"]["weather_code"]]; ?></p>

        <p>Daily Weather:</p>
        <?php foreach ($apiRes['daily']['time'] as $index => $date) { ?>
            <a href="#daily-<?= $index ?>"><?= $date; ?></a><br />
        <?php } ?>
        <do type="prev" label="Back">
            <go href="/weather/" />
        </do>
        <p>Weather data provided by Open-Meteo.com</p>
    </card>
    <?php foreach ($apiRes['daily']['time'] as $index => $date) { ?>
        <card id='daily-<?= $index ?>' title='Daily Weather'>
            <p>Min <?= $apiRes["daily"]["temperature_2m_min"][$index] ?> <?= $apiRes["daily_units"]["temperature_2m_min"]; ?> - Max <?= $apiRes["daily"]["temperature_2m_max"][$index] ?> <?= $apiRes["daily_units"]["temperature_2m_max"]; ?></p>
            <p>Expected Weather: <?= $wv_codes[$apiRes["daily"]["weather_code"][$index]]; ?> - UV Index: <?= round($apiRes["daily"]["uv_index_max"][$index]); ?></p>
            <p>Sunrise: <?= getFormattedTime($apiRes["daily"]["sunrise"][$index]); ?> - Sunset: <?= getFormattedTime($apiRes["daily"]["sunset"][$index]); ?></p>
            <do type="prev" label="Back">
                <go href="#current" />
            </do>
        </card>
    <?php } ?>
</wml>