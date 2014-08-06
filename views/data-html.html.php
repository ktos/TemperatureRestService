<p>Reading: <?= $data; ?> (<?= $datatype; ?>)</p>
<p>Sensor name:<?= $name; ?></p>
<p>Sensor type: <?= $type; ?></p>
<p>Sensor internal id: <?= $id; ?></p>
<p>Description: <?= $description; ?></p>
<p>Last updated on: <?= date('Y-m-d H:i:s', $lastupdated); ?></p>
<p>Status: <?= ((bool)$status)? 'OK' : 'Error' ?></p>