<?php
header('Content-Type: text/vnd.wap.wml'); // Many devices require correct Content-Type.
$searchTerm = $_GET['q'];
$results = json_decode(file_get_contents("https://geocoding-api.open-meteo.com/v1/search?count=10&language=en&format=json&name=" . htmlentities($searchTerm)), true);
?>
<?= '<?xml version="1.0"?>' ?>
<!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml">
<wml>

    <card id='results' title='Search Results'>
        <?php foreach ($results["results"] as $location) { ?>
            <a href="/weather/weather?id=<?= $location['id']; ?>"><?= $location['name']; ?> (<?= $location['admin1']; ?>, <?= $location['country_code']; ?>)</a>
            <br />
        <?php } ?>
        <p>Weather data provided by Open-Meteo.com</p>
        <do type="prev" label="Go Back">
            <go href="/weather/" />
        </do>
    </card>
</wml>