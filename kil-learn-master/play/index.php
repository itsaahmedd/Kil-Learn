<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/MySQLDatabase.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/User.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/Game.php");

$database = MySQLDatabase::getDefault();
$game = Game::getCurrent($database);

// If a game has not been started, or it has already finished, return
if (!isset($game) || !$game->isOngoing()) {
    print("Game doesn't exist or is not ongoing");
//    header("Location: ../dashboard");
    die();
}

$round = $game->getCurrentRound($database);

// Redirect to the game-over page if the game is finished
if (!isset($round)) {
    header("Location: game-over");
    die();
}

if (!$round->hasStarted()) {
    $round->start($database);
}

// If round is no longer ongoing, the time has run out. Move to the next round
if (!$round->isOngoing()) {
    if ($game->nextRound($database)) {
        $round = $game->getCurrentRound($database);

        if (!$round->hasStarted()) {
            $round->start($database);
        }
    } else {
        header("Location: game-over");
        die();
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Game | Kil-Learn</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"
          integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin=""/>
    <link rel="stylesheet" type="text/css" href="../resources/stylesheets/game.css">
    <style>
        #map {
            position: absolute;
            right: 0;
            bottom: 0;
            width: 500px;
            height: 500px;
        }
    </style>
    <script>
        function toggleElement() {
            var element = document.getElementById("map");
            if (element.style.display === "none") {
                element.style.display = "block";
                document.getElementById("toggleBtn").innerHTML = "Hide Map";
            } else {
                element.style.display = "none";
                document.getElementById("toggleBtn").innerHTML = "Show Map";
            }
        }
    </script>
</head>

<body>
<header>
    <nav>
        <ul>
            <li>
                <p>Score: <span id="score"><?php echo $game->getScore($database) ?></span></p>
            </li>
            <li>
                <p>Time: <span id="timer">00:00</span></p>
                <script type="text/javascript">
                    let timer;
                    let secondsLeft = <?php echo $round->getTimeLeft() ?>;
                    let updateTimer = function () {
                        secondsLeft--;
                        let mins = Math.floor(secondsLeft / 60);
                        let seconds = secondsLeft % 60;
                        document.getElementById("timer").innerHTML = (mins < 10 ? "0" : "") + mins + ":" +
                            (seconds < 10 ? "0" : "") + seconds;
                        if (secondsLeft <= 0) {
                            clearInterval(timer);
                            window.location.replace("round-over?reason=timeout");
                        }
                    };

                    updateTimer();
                    timer = setInterval(updateTimer, 1000);
                </script>
            </li>
            <li>
                <button id="toggleBtn" onclick="toggleElement()">Hide Map</button>
            </li>
            <li><a href="../back-end/scripts/game/quit.php">
                    <button id="quit-button">Quit</button>
                </a></li>
        </ul>
    </nav>
</header>
<main>
    <div class="container">
        <div class="img-container"
             style="<?php echo "background-image: url('../" . $round->getRoom()->getImagePath() . "')" ?>"></div>
        <div class="overlay">
        </div>
    </div>
</main>
<div id="map"></div>
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"
        integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script>
<script>
    let wrongSelections = [
        <?php
        session_start();
        $wrongSelections = $_SESSION[Game::$GAME_ROUND_WRONG_SELECTIONS_SESSION_KEY];
        if (isset($wrongSelections)) {
            echo "\"" . implode("\", \"", explode("&", $wrongSelections)) . "\"";
        }
        ?>
    ]

    // Defining  floors and their boundaries
    var imageBounds1 = [[-1100, -1100], [950, 1200]];
    var floors = [
        {
            name: 'Ground Floor',
            imageUrl: '../resources/images/floor-plans/ground-floor.png',
            bounds: [imageBounds1],
            rooms: [
                {label: 'G41 Collab Space', bounds: [[-105, 125], [85, 530]]},
                {label: 'G105', bounds: [[-217, -18], [-30, 125]]},
                {label: 'G23 Computer Lab', bounds: [[-690, 160], [-315, 462]]},
                {label: 'ITS Help Desk', bounds: [[-690, -140], [-368, -70]]}
            ]
        },
        {
            name: 'Lower First Floor',
            imageUrl: '../resources/images/floor-plans/lower-first-floor.png',
            bounds: [imageBounds1],
            rooms: [
                {label: 'Collab 2', bounds: [[55, -80], [258, 101]]},
                {label: 'Collab 1', bounds: [[-349, -80], [55, 101]]},
                {label: 'PGR Lab', bounds: [[55, 404], [322, 601]]},
                {label: 'Lecture Theatre 1.1', bounds: [[-545, 292], [-881, 601]]},
                {label: 'LF8', bounds: [[-615, 57], [-881, 231]]},
                {label: 'LF1 Research Lab', bounds: [[-667, -482], [-881, -232]]}
            ]
        },
        {
            name: 'First Floor',
            imageUrl: '../resources/images/floor-plans/first-floor.png',
            bounds: [imageBounds1],
            rooms: [
                {label: 'Courtyard FF', bounds: [[-290, 20], [300, 362]]},
                {label: 'Super Lab', bounds: [[-417, -340], [398, -50]]},
                {label: 'Lecture Theatre 1.5', bounds: [[-483, -340], [-838, -200]]},
                {label: 'Lecture Theatre 1.4', bounds: [[-483, -200], [-803, -60]]},
                {label: 'Lecture Theatre 1.3', bounds: [[-483, -60], [-803, 83]]}
            ]
        },
        {
            name: 'Second Floor',
            imageUrl: '../resources/images/floor-plans/second-floor.png',
            bounds: [imageBounds1],
            rooms: [
                {label: 'Plant Room', bounds: [[-17, 112], [323, 540]]},
                {label: '2.25A', bounds: [[-350, 163], [-566, 413]]},
                {label: '2.9', bounds: [[-566, 163], [-743, 413]]},
                {label: '2.19', bounds: [[-350, 413], [-566, 715]]},
                {label: 'Courtyard SF', bounds: [[226, -351], [-399, 18]]},
                {label: '2.15', bounds: [[-566, 486], [-743, 679]]}
            ]
        }
    ];

    let floorIndex = 0;
    let floorIndexItem = window.sessionStorage.getItem("FloorPlanIndex");
    if (floorIndexItem != null) {
        floorIndex = parseInt(floorIndexItem)
    }

    // Create the Leaflet map
    var map = L.map('map', {
        crs: L.CRS.Simple,
        minZoom: -2,
        maxZoom: 2,
        maxBounds: imageBounds1
    });

    // Add the base layer
    L.imageOverlay(floors[floorIndex].imageUrl, floors[floorIndex].bounds).addTo(map);

    // Add the floor selection control
    var layerControl = L.control.layers();
    for (var i = 0; i < floors.length; i++) {
        layerControl.addBaseLayer(
            L.imageOverlay(floors[i].imageUrl, floors[i].bounds),
            floors[i].name
        );
    }
    layerControl.addTo(map);

    // Add clickable rectangles for each room
    var roomLayers = [];
    for (var i = 0; i < floors.length; i++) {
        var layerGroup = L.layerGroup();
        for (var j = 0; j < floors[i].rooms.length; j++) {
            let name = floors[i].rooms[j].label;
            var bounds = floors[i].rooms[j].bounds;
            let wrongSelection = wrongSelections.includes(name);
            let color = wrongSelection ? "#ff3333" : "#3388ff";
            var rectangle = L.rectangle(bounds, {color: color, weight: 1});
            rectangle.bindPopup(name);
            if (!wrongSelection) {
                rectangle.on("click", function (e) {
                    window.location.replace("../back-end/scripts/game/guess.php?room=" + name)
                })
            }
            layerGroup.addLayer(rectangle);
        }
        roomLayers.push(layerGroup);
    }

    // Set the initial active room layer
    roomLayers[floorIndex].addTo(map);

    // Change the active room layer when the floor layer changes
    map.on('baselayerchange', function (event) {
        for (var i = 0; i < floors.length; i++) {
            if (floors[i].name === event.name) {
                roomLayers[i].addTo(map);
                window.sessionStorage.setItem("FloorPlanIndex", i)
            } else {
                map.removeLayer(roomLayers[i]);
            }
        }
    });
    map.fitBounds(imageBounds1);
</script>
</body>

</html>