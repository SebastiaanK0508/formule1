<?php
$api_url_drivers = 'https://ergast.com/api/f1/current/driverStandings.json';
$api_url_constructors = 'https://ergast.com/api/f1/current/constructorStandings.json';

// Haal coureursstanden op
$drivers_json = @file_get_contents($api_url_drivers);
$drivers_data = $drivers_json ? json_decode($drivers_json, true) : null;

// Haal constructeursstanden op
$constructors_json = @file_get_contents($api_url_constructors);
$constructors_data = $constructors_json ? json_decode($constructors_json, true) : null;
?>

<h2>F1 Coureurs Kampioenschap</h2>
<?php if ($drivers_data && !empty($drivers_data['MRData']['StandingsTable']['StandingsLists'])) : ?>
    <table>
        <thead>
            <tr><th>Pos</th><th>Coureur</th><th>Team</th><th>Punten</th></tr>
        </thead>
        <tbody>
            <?php foreach ($drivers_data['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'] as $driver) : ?>
                <tr>
                    <td><?php echo $driver['position']; ?></td>
                    <td><?php echo $driver['Driver']['givenName'] . ' ' . $driver['Driver']['familyName']; ?></td>
                    <td><?php echo $driver['Constructors'][0]['name']; ?></td>
                    <td><?php echo $driver['points']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>
    <p>Geen coureurs klassement data beschikbaar op dit moment.</p>
<?php endif; ?>

<h2>F1 Constructeurs Kampioenschap</h2>
<?php if ($constructors_data && !empty($constructors_data['MRData']['StandingsTable']['StandingsLists'])) : ?>
    <table>
        <thead>
            <tr><th>Pos</th><th>Team</th><th>Punten</th></tr>
        </thead>
        <tbody>
            <?php foreach ($constructors_data['MRData']['StandingsTable']['StandingsLists'][0]['ConstructorStandings'] as $constructor) : ?>
                <tr>
                    <td><?php echo $constructor['position']; ?></td>
                    <td><?php echo $constructor['Constructor']['name']; ?></td>
                    <td><?php echo $constructor['points']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>
    <p>Geen constructeurs klassement data beschikbaar op dit moment.</p>
<?php endif; ?>