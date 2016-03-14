<tile>
  <visual branding="logo">

    <binding template="TileSmall" hint-textStacking="center">
      <text hint-style="base" hint-align="center"><?= round((float)$data, 1);?>°C</text>
    </binding>

    <binding template="TileMedium">
      <text hint-style="title"><?= round((float)$data, 1);?>°C</text>
      <text hint-style="bodySubtle"><?= $name ?></text>
    </binding>

    <binding template="TileWide">
      <text hint-style="title"><?= round((float)$data, 1);?>°C</text>
      <text hint-style="bodySubtle"><?= $name ?></text>
      <text hint-style="captionSubtle">last update: <?= date('Y-m-d H:i:s', $lastupdated); ?></text>
    </binding>

  </visual>
</tile>
