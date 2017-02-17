<!DOCTYPE html>
<html lang="en">
  <head>
    <link rel="stylesheet" href="/library/com/leaflet-0.7.1/leaflet.css">
    <link rel="stylesheet" href="/library/com/leaflet-0.7.1/leaflet.markercluster.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="print.css" media="print">
    <script src="/template/js/jquery-1.7.1.min.js"></script>
    <meta charset="utf-8">
    <title>Live Webcast Viewers</title>
  </head>
  <body>
    <main>
      <div id="map"></div>
    </main>
    <section class="sidebar">
      <header id="title" class="sidebar-container">
        <h1>Live Webcast Viewers</h1>
        <p><?php print date('l, F j, Y'); ?> <time></time></p>
      </header>
      <section id="video" class="sidebar-container">
        <video src="mplive?streamer=rtmp://video2.wr.usgs.gov/live" width="500" height="281" controls></video>
      </section>
      <section id="status" class="sidebar-container">
        <h2>Loading&hellip;</h2>
        <p class="expand hide" title="Expand table">Expand/Collapse</p>
      </section>
      <section id="details" class="sidebar-container">
        <div class="fade-top"></div>
        <table></table>
        <div class="fade-bottom"></div>
      </section>
      <section id="alerts" class="sidebar-container"></section>
      <section id="options" class="sidebar-container">
        <input type="checkbox" id="extent" checked="checked">
        <label for="extent">Reposition map when webcast viewers are updated</label>
      </section>
    </section>
    <script src="/library/com/leaflet-0.7.1/leaflet.js"></script>
    <script src="/library/com/leaflet-0.7.1/leaflet.markercluster.js"></script>
    <script src="/template/widgets/jwplayer/jwplayer.js"></script>
    <script src="/template/widgets/jwplayer/script.js"></script>
    <script src="script.js"></script>
  </body>
</html>
